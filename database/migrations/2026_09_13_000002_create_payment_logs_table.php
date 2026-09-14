<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('gateway', 50);                         // zarinpal, saman, sadad, zibal
            $table->string('action', 30);                          // token_request, verify, callback
            $table->string('status', 20);                          // success, failed, pending
            $table->json('request_payload')->nullable();            // sent to gateway (no sensitive data)
            $table->json('response_payload')->nullable();           // gateway response
            $table->text('error_message')->nullable();
            $table->integer('amount')->nullable();                  // amount in toman
            $table->string('reference_id')->nullable();             // RefID from gateway
            $table->string('ip', 45)->nullable();
            $table->integer('response_time_ms')->nullable();        // response time in ms
            $table->timestamps();

            // Composite indexes for dashboard queries
            $table->index(['gateway', 'created_at']);
            $table->index(['order_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
