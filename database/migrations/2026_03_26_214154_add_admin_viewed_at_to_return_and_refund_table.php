<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_and_refunds', function (Blueprint $table) {
            $table->timestamp('admin_viewed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('return_and_refunds', function (Blueprint $table) {
            $table->dropColumn('admin_viewed_at');
        });
    }
};
