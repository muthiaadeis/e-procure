<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // input   = User B, yang membuat/input MR
            // approver_a = User A, approval tahap 1
            // approver_c = User C, approval tahap 2
            // finance = User D, yang menandai status lunas/belum lunas
            $table->enum('role', ['input', 'approver_a', 'approver_c', 'finance'])
                ->default('input')
                ->after('is_approver');
        });

        // Migrasi data lama: user yang sebelumnya is_approver = true
        // sementara dianggap approver_a supaya tidak kehilangan akses approve.
        // Silakan sesuaikan manual lewat halaman User untuk menentukan
        // siapa User A, User C, dan User D yang sebenarnya.
        DB::table('users')->where('is_approver', true)->update(['role' => 'approver_a']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
