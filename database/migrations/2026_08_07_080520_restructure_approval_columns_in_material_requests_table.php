<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename kolom approval lama menjadi kolom approval tahap A
        Schema::table('material_requests', function (Blueprint $table) {
            $table->renameColumn('approval_by', 'approved_a_by');
            $table->renameColumn('approved_at', 'approved_a_at');
        });

        // 2. Tambah kolom approval tahap C, dibuat oleh (User B), dan ditandai lunas oleh (User D)
        Schema::table('material_requests', function (Blueprint $table) {
            $table->foreignId('approved_c_by')->nullable()->after('approved_a_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_c_at')->nullable()->after('approved_c_by');

            $table->foreignId('created_by')->nullable()->after('id')
                ->constrained('users')->nullOnDelete();

            $table->foreignId('paid_by')->nullable()->after('paid_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paid_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('approved_c_by');
            $table->dropColumn('approved_c_at');
        });

        Schema::table('material_requests', function (Blueprint $table) {
            $table->renameColumn('approved_a_at', 'approved_at');
            $table->renameColumn('approved_a_by', 'approval_by');
        });
    }
};
