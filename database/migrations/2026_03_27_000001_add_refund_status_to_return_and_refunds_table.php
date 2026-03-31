<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_and_refunds', function (Blueprint $table) {
            // null = not yet applicable, 5 = awaiting item, 10 = item received, 15 = refund issued
            $table->unsignedTinyInteger('refund_status')->nullable()->after('status');
            $table->timestamp('refund_issued_at')->nullable()->after('refund_status');
        });
    }

    public function down(): void
    {
        Schema::table('return_and_refunds', function (Blueprint $table) {
            $table->dropColumn(['refund_status', 'refund_issued_at']);
        });
    }
};
