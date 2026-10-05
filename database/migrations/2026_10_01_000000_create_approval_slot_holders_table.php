<?php
// database/migrations/2026_10_01_000000_create_approval_slot_holders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Siapa saja yang memegang tiap posisi tanda tangan (mis. "General Manager")
        // di PR dan PO. Diatur oleh administrator. Satu posisi boleh dipegang lebih
        // dari satu orang (mis. ada pengganti), dan satu orang boleh memegang beberapa posisi.
        Schema::create('approval_slot_holders', function (Blueprint $table) {
            $table->id();
            $table->string('role_label'); // mis. "General Manager", sama dengan role_label di template PR / PO
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_label', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_slot_holders');
    }
};
