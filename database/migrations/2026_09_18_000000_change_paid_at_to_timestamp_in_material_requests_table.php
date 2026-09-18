<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // paid_at dulu cuma kolom `date` (gak nyimpen jam), padahal ditampilin
    // sekarang pakai format jam (H:i) biar konsisten sama approved_a_at /
    // approved_c_at / rejected_at yang lain. Ganti jadi timestamp biar jamnya
    // beneran ke-save, bukan cuma 00:00.
    // Pakai raw SQL (bukan ->change()) biar gak perlu install doctrine/dbal.
    public function up(): void
    {
        DB::statement('ALTER TABLE material_requests MODIFY paid_at TIMESTAMP NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE material_requests MODIFY paid_at DATE NULL');
    }
};
