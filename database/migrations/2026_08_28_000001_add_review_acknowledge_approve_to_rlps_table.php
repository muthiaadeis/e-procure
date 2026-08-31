<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alur tanda tangan RRP: Prepared By (created_by, udah ada) -> Review By ->
        // Acknowledge By -> Approved By. Berurutan, sama kayak alur Approval 1/2 di MR.
        Schema::table('rlps', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->after('created_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            $table->foreignId('acknowledged_by')->nullable()->after('reviewed_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable()->after('acknowledged_by');

            $table->foreignId('approved_by')->nullable()->after('acknowledged_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('rlps', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('reviewed_at');

            $table->dropConstrainedForeignId('acknowledged_by');
            $table->dropColumn('acknowledged_at');

            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
        });
    }
};
