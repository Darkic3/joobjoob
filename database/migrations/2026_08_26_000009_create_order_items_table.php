<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity')->nullable();
            $table->string('old_quantity')->nullable()->comment('تعداد قبل از مرجوعی');
            $table->double('weight')->nullable()->comment('وزن آیتم (گرم)');
            $table->string('price')->nullable()->comment('قیمت واحد (تومان)');
            $table->string('discounted_price')->nullable()->comment('قیمت با تخفیف (تومان)');
            $table->softDeletes();
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
