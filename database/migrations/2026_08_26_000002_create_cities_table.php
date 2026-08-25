<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('cod24_id')->nullable()->comment('شناسه Cod24 برای API ارسال');
            $table->integer('chapar_id')->nullable()->comment('شناسه چاپار برای API ارسال');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
