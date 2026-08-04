<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->double('price')->default(0);
            $table->double('discounted_price')->default(0);
            $table->double('final_price')->default(0);
            $table->integer('stock')->default(0);
            $table->double('weight')->default(100);
            $table->softDeletes();
            $table->timestamps();

            $table->index('product_id');
            $table->index('final_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
