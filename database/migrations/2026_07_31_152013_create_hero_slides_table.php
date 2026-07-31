<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();

            $table->json('badge')->nullable();
            $table->json('title');
            $table->json('desc')->nullable();
            $table->json('btn_text')->nullable();

            $table->json('price')->nullable();
            $table->json('old_price')->nullable();

            $table->string('image');
            $table->string('btn_link');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
