<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Baru bisa pasang FK sekarang karena tabel rlp_vendors sudah ada
        Schema::table('rlps', function (Blueprint $table) {
            $table->foreign('selected_vendor_id')
                ->references('id')->on('rlp_vendors')
                ->nullOnDelete();
        });

        // Tabel WUR Cost Estimasi (job description, quantity, u/price, total per baris)
        Schema::create('rlp_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rlp_id')->constrained('rlps')->cascadeOnDelete();
            $table->string('job_description');
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('u_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rlp_costs');

        Schema::table('rlps', function (Blueprint $table) {
            $table->dropForeign(['selected_vendor_id']);
        });
    }
};
