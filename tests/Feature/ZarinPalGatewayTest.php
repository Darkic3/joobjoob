<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Order;
use App\Models\User;
use App\Payments\PaymentPostDTO;
use App\Payments\ZarinPal;
use App\Payments\ZarinPalErrorMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * تست درگاه زرین‌پال (REST v4) با HTTP فیک.
 *
 * قراردادها (طبق https://www.zarinpal.com/docs/paymentGateway/connectToGateway):
 *  - ورودی‌ها تومان‌اند و ×۱۰ به ریال تبدیل می‌شوند (currency=IRR).
 *  - Status=NOK یعنی لغو توسط کاربر و نباید verify صدا زده شود.
 *  - کد 100 موفق، 101 موفقِ تکراری (idempotent).
 */
class ZarinPalGatewayTest extends TestCase
{
    use RefreshDatabase;

    private const MERCHANT = '11111111-2222-3333-4444-555555555555';

    private User $user;

    private Bank $bank;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.zarinpal.merchant_id' => self::MERCHANT,
            'services.zarinpal.sandbox' => false,
            'services.zarinpal.currency' => 'IRR',
            'monitoring.log_payments' => false,
        ]);

        $this->user = User::factory()->create();
        $this->bank = Bank::create([
            'title' => 'زرین‌پال تست',
            'bank_type' => 'zarinPal',
            'status' => 1,
            'config' => ['MerchantId' => self::MERCHANT],
        ]);
    }

    private function makeOrder(int $paymentToman = 150000): Order
    {
        return Order::create([
            'user_id' => $this->user->id,
            'bank_id' => $this->bank->id,
            'order_status' => 'paying',
            'payment_price' => (string) $paymentToman,
        ]);
    }

    private function gateway(): ZarinPal
    {
        return new ZarinPal($this->bank, 'Shopora');
    }

    // ── تبدیل واحد ──

    public function test_toman_converted_to_rial_explicitly(): void
    {
        $this->assertSame(1500000, ZarinPal::toRial(150000));
        $this->assertSame(0, ZarinPal::toRial(0));
        $this->assertSame(0, ZarinPal::toRial(-5));
    }

    // ── درخواست توکن ──

    public function test_request_sends_rial_currency_and_metadata(): void
    {
        $order = $this->makeOrder(150000);

        Http::fake([
            'payment.zarinpal.com/*' => Http::response([
                'data' => ['code' => 100, 'authority' => 'A00000000000000000000000000217885159', 'message' => 'Success'],
                'errors' => [],
            ], 200),
        ]);

        $dto = $this->gateway()->getBankToken(150000, $order);

        $this->assertSame('success', $dto->getStatus());
        $this->assertSame('A00000000000000000000000000217885159', $dto->getPostData()['Authority']);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->url() === 'https://payment.zarinpal.com/pg/v4/payment/request.json'
                && $payload['merchant_id'] === self::MERCHANT
                && $payload['amount'] === 1500000
                && $payload['currency'] === 'IRR'
                && ($payload['metadata']['order_id'] ?? null) === (string) $payload['metadata']['order_id']
                && ! empty($payload['callback_url'])
                && ! empty($payload['description']);
        });
    }

    public function test_request_failure_returns_gateway_error_code(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            'payment.zarinpal.com/*' => Http::response([
                'data' => [],
                'errors' => [['code' => -11, 'message' => 'Merchant not found']],
            ], 200),
        ]);

        $dto = $this->gateway()->getBankToken(150000, $order);

        $this->assertSame('failed', $dto->getStatus());
        $this->assertSame(-11, $dto->getPostData()['ErrorCode']);
    }

    public function test_request_rejects_non_positive_amount_without_http(): void
    {
        $order = $this->makeOrder();

        $dto = $this->gateway()->getBankToken(0, $order);

        $this->assertSame('failed', $dto->getStatus());
        Http::assertNothingSent();
    }

    // ── وریفای ──

    public function test_verify_success_code_100(): void
    {
        $order = $this->makeOrder(150000);

        Http::fake([
            'payment.zarinpal.com/*' => Http::response([
                'data' => [
                    'code' => 100, 'message' => 'Verified', 'ref_id' => 201,
                    'card_pan' => '502229******5995', 'card_hash' => 'abc',
                    'fee_type' => 'Merchant', 'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);

        $dto = $this->gateway()->verifyTransaction(
            ['Authority' => 'A00000000000000000000000000217885159', 'Status' => 'OK'],
            150000,
            $order
        );

        $this->assertSame('success', $dto->getStatus());
        $this->assertSame('201', (string) $dto->getVerifyData()['RefID']);
        $this->assertFalse($dto->getVerifyData()['AlreadyVerified']);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            // مبلغ وریفای همیشه از سفارش (تومان→ریال)، نه از کال‌بک
            return $request->url() === 'https://payment.zarinpal.com/pg/v4/payment/verify.json'
                && $payload['amount'] === 1500000
                && $payload['authority'] === 'A00000000000000000000000000217885159';
        });
    }

    public function test_verify_code_101_is_idempotent_success(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            'payment.zarinpal.com/*' => Http::response([
                'data' => ['code' => 101, 'message' => 'Already verified', 'ref_id' => 201],
                'errors' => [],
            ], 200),
        ]);

        $dto = $this->gateway()->verifyTransaction(['Authority' => 'AAA', 'Status' => 'OK'], 150000, $order);

        $this->assertSame('success', $dto->getStatus());
        $this->assertTrue($dto->getVerifyData()['AlreadyVerified']);
    }

    public function test_verify_not_called_when_user_cancelled(): void
    {
        $order = $this->makeOrder();

        $dto = $this->gateway()->verifyTransaction(['Authority' => 'AAA', 'Status' => 'NOK'], 150000, $order);

        $this->assertSame('failed', $dto->getStatus());
        $this->assertTrue($dto->getVerifyData()['CancelledByUser']);
        Http::assertNothingSent();
    }

    public function test_verify_failure_maps_error_message(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            'payment.zarinpal.com/*' => Http::response([
                'data' => ['code' => -51, 'message' => 'Unsuccessful'],
                'errors' => [],
            ], 200),
        ]);

        $dto = $this->gateway()->verifyTransaction(['Authority' => 'AAA', 'Status' => 'OK'], 150000, $order);

        $this->assertSame('failed', $dto->getStatus());
        $this->assertSame(-51, $dto->getVerifyData()['ErrorCode']);
        $this->assertNotEmpty($dto->getVerifyData()['ErrorMessage']);
    }

    // ── ریدایرکت و sandbox ──

    public function test_redirect_url_points_to_startpay(): void
    {
        $dto = PaymentPostDTO::fromData('success', ['Authority' => 'A123']);

        $this->assertSame(
            'https://payment.zarinpal.com/pg/StartPay/A123',
            $this->gateway()->getRedirectUrl($dto)
        );
    }

    public function test_sandbox_switches_endpoints(): void
    {
        config(['services.zarinpal.sandbox' => true]);

        $order = $this->makeOrder();

        Http::fake([
            'sandbox.zarinpal.com/*' => Http::response([
                'data' => ['code' => 100, 'authority' => 'S00000000000000000000000000217885159'],
                'errors' => [],
            ], 200),
        ]);

        $dto = $this->gateway()->getBankToken(150000, $order);

        $this->assertSame('success', $dto->getStatus());
        $this->assertStringStartsWith(
            'https://sandbox.zarinpal.com/pg/StartPay/',
            $this->gateway()->getRedirectUrl($dto)
        );
    }

    public function test_missing_merchant_throws_clear_exception(): void
    {
        config(['services.zarinpal.merchant_id' => null]);

        $bank = Bank::create([
            'title' => 'بدون مرچنت',
            'bank_type' => 'zarinPal',
            'status' => 1,
            'config' => [],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/ZARINPAL_MERCHANT_ID/');

        new ZarinPal($bank);
    }

    // ── مپر خطا ──

    public function test_error_mapper_covers_documented_codes(): void
    {
        $this->assertStringContainsString('ناموفق', ZarinPalErrorMapper::message(-51));
        $this->assertNotEmpty(ZarinPalErrorMapper::message(-11));
        $this->assertNotEmpty(ZarinPalErrorMapper::message(-53));
        $this->assertStringContainsString('-999', ZarinPalErrorMapper::message(-999));
    }
}
