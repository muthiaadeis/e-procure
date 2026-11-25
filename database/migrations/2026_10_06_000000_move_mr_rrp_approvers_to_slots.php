<?php
// database/migrations/2026_10_06_000000_move_mr_rrp_approvers_to_slots.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom role sebelumnya ENUM terbatas; jadikan string biasa.
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(30) NOT NULL DEFAULT 'input'");

        // Pindahkan approver/finance lama ke tabel posisi supaya data yang sudah ada tidak hilang.
        $map = [
            'approver_a' => 'MR Approval 1',
            'approver_c' => 'MR Approval 2',
            'finance'    => 'MR Finance',
        ];

        foreach (DB::table('users')->whereIn('role', array_keys($map))->get() as $u) {
            DB::table('approval_slot_holders')->updateOrInsert(
                ['role_label' => $map[$u->role], 'user_id' => $u->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        DB::table('users')
            ->whereIn('role', array_keys($map))
            ->update(['role' => 'approver', 'is_approver' => true]);
    }

    public function down(): void
    {
        // Tidak dibalik: pemetaan posisi -> role lama tidak satu-satu.
    }
};
