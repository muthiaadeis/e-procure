<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pindahkan data description/quantity/unit yang sudah ada ke mr_items,
        //    supaya data lama tidak hilang.
        $old = DB::table('material_requests')->select('id', 'description', 'quantity', 'unit')->get();

        foreach ($old as $mr) {
            DB::table('mr_items')->insert([
                'material_request_id' => $mr->id,
                'description' => $mr->description,
                'quantity' => $mr->quantity,
                'unit' => $mr->unit,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Hapus kolom lama dari material_requests karena sekarang disimpan per item.
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn(['description', 'quantity', 'unit']);
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->text('description')->nullable()->after('charge_to');
            $table->integer('quantity')->nullable()->after('description');
            $table->string('unit')->nullable()->after('quantity');
        });

        // Ambil item pertama tiap MR sebagai representasi data lama (best effort).
        $items = DB::table('mr_items')->orderBy('id')->get()->groupBy('material_request_id');

        foreach ($items as $materialRequestId => $group) {
            $first = $group->first();
            DB::table('material_requests')->where('id', $materialRequestId)->update([
                'description' => $first->description,
                'quantity' => $first->quantity,
                'unit' => $first->unit,
            ]);
        }
    }
};
