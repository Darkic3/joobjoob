# Payment ZarinPal Workflow

> **Source:** `app/Payments/ZarinPal.php`, `PaymentGatewayFactory.php`, `BankPaymentService.php`, `OrderService.php:133-220`, `CheckoutController::finishZarinPal`

## Factory
```php
PaymentGatewayFactory::create(Bank $bank) match(bank_type): zarinPal=>ZarinPal, card_to_card=>ZarinPal (dead code, handled before factory)
```

## ZarinPal details
- MerchantId from `banks.config` JSON: `$config['MerchantId']`
- Endpoints: `https://payment.zarinpal.com/pg/v4/payment/request.json`, `verify.json`, `StartPay/`
- Callback: `route('checkout.finish.zarin-pal')` = `/checkout/finish` (Route::any, no auth)
- **Unit: Toman -> Rial via `intval($amount . '0')` (x10 string concat)** — evidence `ZarinPal.php:20,27` and `ShippingCalculator.php:24`
- Timeout 30s via Http::withHeaders()->timeout(30)

## Flow
```
OrderService::checkout -> BankPaymentService::handlePayment(bank, order, price):
  gateway = Factory::create(bank)
  response = gateway->getBankToken(price, order):
    amountRial = intval(amount.'0')
    payload {merchant_id, amount:amountRial, callback_url, description: siteName+' خرید از '+siteName, metadata:[]}
    POST request.json -> if not ok -> failed; if data.code===100 -> success {Authority}
  Auth::loginUsingId(order->user_id)
  if failed -> order->delete() (hard delete!)
  else update transaction_info.post = response.postData, verify=[]
       ob_start gateway->redirect(response, order) -> header Location StartPay/Authority

Callback: CheckoutController::finishZarinPal -> OrderService::callback('transaction_info->post->Authority', Authority, input):
  findByField(Authority) -> if not found redirect cart
  if already paid/deposit_paid -> redirect success (idempotent)
  price same calc, BankPaymentService::handleVerify(order, callbackData, price):
    if callback Status !== 'OK' -> failed
    payload {merchant_id, authority, amount:amountRial}
    POST verify.json -> code 100 or 101 -> success {RefID} else failed
    update transaction_info.verify
    if failed -> order_status unpaid else -> keep
  if success -> handleSuccessOrderPayment: update paid/deposit_paid, Audit log, notifyUser+notifyAdmins
  else handleFailedPayment: update unpaid, restoreStock
```

## Duplicate handling
If order already paid -> redirect success immediately, no second verify.

## AI gotcha
- Always use `'0'` concat for unit conversion.
- Don't forget transaction_info JSON structure {post, verify}.
- Hard delete on token failure is risky (FIXED 2026-09-07 — see below).

## P0-03 Fix — Token Failure (2026-09-07)

**Ghabli (STALE):** `if failed -> order->delete() (hard delete!)` — `BankPaymentService.php:26`.

**Feli (FIXED):** `BankPaymentService.php:27-51`
```
if failed ->
  try StockService::restoreStock(order) catch Log
  update order_status=unpaid
  AuditService::orderStatusChanged(paying -> unpaid, 'Gateway token failed')
  redirect checkout.failed
```
> Natije: Order baraye debug/retry mimanad, stock barmigardad, audit trail hefz.

## Doc Drift Warning
In workflow ghablan `hard delete` ra mostanad karde bood — alan bayad `unpaid` flow ra jaygozin koni.
