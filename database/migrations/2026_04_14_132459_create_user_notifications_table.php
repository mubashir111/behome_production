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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // null = broadcast to all users
            $table->string('title', 200);
            $table->string('body', 500)->nullable();
            $table->string('icon', 50)->default('bell');
            $table->string('color', 20)->default('#6366f1');
            $table->string('link', 500)->nullable();
            $table->string('type', 50)->default('info'); // info, success, warning, promo
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
