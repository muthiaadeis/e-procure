<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom remarks di mr_items (remarks sekarang per item, bukan per MR).
        Schema::table('mr_items', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('unit');
        });

        // 2. Best effort: salin remarks lama (di material_requests) ke item pertama tiap MR,
        //    supaya data lama tidak hilang begitu saja.
        $old = DB::table('material_requests')->whereNotNull('remarks')->select('id', 'remarks')->get();

        foreach ($old as $mr) {
            $firstItemId = DB::table('mr_items')
                ->where('material_request_id', $mr->id)
                ->orderBy('id')
                ->value('id');

            if ($firstItemId) {
                DB::table('mr_items')->where('id', $firstItemId)->update([
                    'remarks' => $mr->remarks,
                ]);
            }
        }

        // 3. Hapus kolom remarks lama dari material_requests.
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('charge_to');
        });

        Schema::table('mr_items', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
