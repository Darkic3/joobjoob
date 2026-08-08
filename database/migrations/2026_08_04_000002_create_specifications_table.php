<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->enum('type', ['text', 'select'])->default('text');
            $table->foreignId('parent_id')->nullable()->constrained('specifications')->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->boolean('is_filter')->default(false);
            $table->boolean('is_color')->default(false);
            $table->string('color_code')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('parent_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specifications');
    }
};
