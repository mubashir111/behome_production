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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Reverting to decimal(13, 6) nullable as seen in other tables? 
            // Or just leave it as decimal(10, 2) since we don't know the exact original type (but other tables have 13, 6)
            $table->decimal('discount', 13, 6)->nullable()->default(0)->change();
        });
    }
};
