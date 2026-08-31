<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // حذف جدول قدیمی orders اگر وجود دارد
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->text('address')->nullable()->comment('JSON: state, city, address, receiptor_mobile, postal_code');
            $table->string('receiptor_full_name')->nullable();
            $table->foreignId('shipping_status_id')->nullable()->constrained('order_shipping_statuses')->nullOnDelete();
            $table->string('order_status')->nullable()->comment('paying|paid|deposit_paid|unpaid|cancelled|wait_for_verification');
            $table->integer('freight_balance')->default(0)->comment('1=پس کرایه');
            $table->foreignId('basket_id')->nullable()->comment('مرجع سبد خرید');
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete();
            $table->foreignId('discount_id')->nullable();
            $table->double('total_weight')->nullable()->comment('وزن کل (گرم)');
            $table->string('post_code')->nullable()->comment('کد پیگیری پستی');
            $table->string('shipping_price')->nullable()->comment('هزینه ارسال (تومان)');
            $table->string('total_price')->nullable()->comment('قیمت کل');
            $table->string('discount_price')->nullable()->comment('مبلغ تخفیف');
            $table->string('payment_price')->nullable()->comment('مبلغ قابل پرداخت');
            $table->string('tax_price')->nullable()->comment('مبلغ مالیات');
            $table->string('current_tax')->nullable()->comment('درصد مالیات');
            $table->string('deposit_price')->nullable()->comment('مبلغ بیعانه');
            $table->string('remaining_price')->nullable()->comment('مبلغ باقیمانده');
            $table->text('user_description')->nullable()->comment('توضیحات کاربر');
            $table->json('transaction_info')->nullable()->comment('اطلاعات تراکنش (post + verify)');
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
