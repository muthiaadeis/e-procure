<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->foreignId('rejected_a_by')->nullable()->after('approved_a_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_a_at')->nullable()->after('rejected_a_by');
            $table->text('rejection_a_reason')->nullable()->after('rejected_a_at');

            $table->foreignId('rejected_c_by')->nullable()->after('approved_c_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_c_at')->nullable()->after('rejected_c_by');
            $table->text('rejection_c_reason')->nullable()->after('rejected_c_at');

            $table->foreignId('finance_rejected_by')->nullable()->after('paid_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('finance_rejected_at')->nullable()->after('finance_rejected_by');
            $table->text('finance_rejection_reason')->nullable()->after('finance_rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('finance_rejected_by');
            $table->dropColumn(['finance_rejected_at', 'finance_rejection_reason']);

            $table->dropConstrainedForeignId('rejected_c_by');
            $table->dropColumn(['rejected_c_at', 'rejection_c_reason']);

            $table->dropConstrainedForeignId('rejected_a_by');
            $table->dropColumn(['rejected_a_at', 'rejection_a_reason']);
        });
    }
};
