<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom sementara buat nyimpen id user hasil mapping nama lama
        Schema::table('material_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('approval_by_new')->nullable()->after('approval_by');
        });

        // 2. Mapping nama lama (string) ke id user yang namanya sama persis
        $map = DB::table('users')->pluck('id', 'name'); // ['Suparlan' => 1, ...]
        foreach ($map as $name => $id) {
            DB::table('material_requests')->where('approval_by', $name)->update(['approval_by_new' => $id]);
        }

        // 3. Hapus kolom lama, ganti nama kolom baru jadi approval_by, tambah approved_at
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn('approval_by');
        });

        Schema::table('material_requests', function (Blueprint $table) {
            $table->renameColumn('approval_by_new', 'approval_by');
            $table->timestamp('approved_at')->nullable()->after('approval_by');
        });

        Schema::table('material_requests', function (Blueprint $table) {
            $table->foreign('approval_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropForeign(['approval_by']);
            $table->dropColumn(['approved_at']);
        });

        Schema::table('material_requests', function (Blueprint $table) {
            $table->enum('approval_by', ['Suparlan', 'Dedi Novendri', 'Nurmila Sari M'])->change();
        });
    }
};
