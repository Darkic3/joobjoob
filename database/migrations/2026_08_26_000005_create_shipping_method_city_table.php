<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_method_city', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();

            $table->primary(['shipping_method_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_method_city');
    }
};
