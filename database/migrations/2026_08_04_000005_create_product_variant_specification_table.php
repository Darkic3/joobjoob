<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_specification', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('specification_id')->constrained('specifications')->cascadeOnDelete();

            $table->index(['product_variant_id', 'specification_id'], 'pvs_pv_spec_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_specification');
    }
};
