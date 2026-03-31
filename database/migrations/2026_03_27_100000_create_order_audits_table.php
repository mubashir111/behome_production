<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('event', 60);                   // e.g. order_placed, status_changed, payment_confirmed
            $table->string('description');                 // human-readable summary
            $table->json('meta')->nullable();              // old/new values, reasons, amounts, etc.
            $table->string('actor_type', 20)->default('system'); // admin | customer | system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_audits');
    }
};
