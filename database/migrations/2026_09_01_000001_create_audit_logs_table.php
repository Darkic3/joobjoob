<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event')->index();              // order.created, order.status_changed, product.stock_changed, etc.
            $table->string('auditable_type')->index();      // App\Models\Order, App\Models\Product
            $table->unsignedBigInteger('auditable_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // who performed the action
            $table->json('old_values')->nullable();          // previous state
            $table->json('new_values')->nullable();          // new state
            $table->json('meta')->nullable();                // extra context (items, discount, shipping, etc.)
            $table->text('description')->nullable();         // human-readable description
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
