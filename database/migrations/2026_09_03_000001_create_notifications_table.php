<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('type');         // e.g. order.created, order.paid
            $table->string('title');        // short title (Persian)
            $table->text('body')->nullable();
            $table->string('icon')->nullable();  // emoji or icon name
            $table->string('link')->nullable();  // URL destination
            $table->boolean('is_read')->default(false);
            $table->json('data')->nullable();    // extra context (order_id, amount, etc.)
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id', 'is_read']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
