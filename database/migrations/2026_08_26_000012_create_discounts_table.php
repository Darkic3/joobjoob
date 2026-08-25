<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('amount')->nullable()->comment('مبلغ یا درصد تخفیف');
            $table->string('type')->nullable()->comment('cash|percent');
            $table->integer('count')->default(1)->comment('تعداد دفعات قابل استفاده');
            $table->string('basket_minimum_price')->nullable()->comment('حداقل قیمت سبد برای فعال شدن');
            $table->tinyInteger('first_purchase')->default(0)->comment('فقط خرید اول');
            $table->tinyInteger('with_discount')->default(0)->comment('آیا روی محصولات تخفیف‌دار اعمال شود؟');
            $table->integer('user_id')->nullable()->comment('مخصوص یک کاربر خاص');
            $table->integer('max_usage_per_user')->default(1)->comment('حداکثر استفاده توسط هر کاربر');
            $table->string('pay_type')->nullable()->comment('category|brand|product — نوع اعمال تخفیف');
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
