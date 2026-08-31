<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vendor quotation sekarang nempel ke satu RRP Item spesifik (bukan berdiri sendiri lagi).
        // Item Name, PN, Qty, UOM ikut item induknya, dan Brand dihapus (cuma 1 brand per No RRP).
        Schema::table('rlp_vendors', function (Blueprint $table) {
            $table->foreignId('rlp_item_id')->nullable()->after('rlp_id')
                ->constrained('rlp_items')->cascadeOnDelete();
        });

        Schema::table('rlp_vendors', function (Blueprint $table) {
            $table->dropForeign(['rlp_id']);
            $table->dropColumn(['rlp_id', 'item_name', 'part_number', 'qty', 'brand']);
        });

        // "Vendor terpilih" & revenue sekarang dihitung per item, bukan per RRP keseluruhan.
        Schema::table('rlp_items', function (Blueprint $table) {
            $table->foreignId('selected_vendor_id')->nullable()->after('part_catalog_ext_price')
                ->constrained('rlp_vendors')->nullOnDelete();
            $table->decimal('revenue_ext_price', 15, 2)->default(0)->after('selected_vendor_id');
        });

        Schema::table('rlps', function (Blueprint $table) {
            $table->dropForeign(['selected_vendor_id']);
            $table->dropColumn('selected_vendor_id');
        });
    }

    public function down(): void
    {
        Schema::table('rlps', function (Blueprint $table) {
            $table->unsignedBigInteger('selected_vendor_id')->nullable();
        });

        Schema::table('rlp_items', function (Blueprint $table) {
            $table->dropForeign(['selected_vendor_id']);
            $table->dropColumn(['selected_vendor_id', 'revenue_ext_price']);
        });

        Schema::table('rlp_vendors', function (Blueprint $table) {
            $table->foreignId('rlp_id')->nullable()->after('id')->constrained('rlps')->cascadeOnDelete();
            $table->string('item_name')->nullable();
            $table->string('part_number')->nullable();
            $table->integer('qty')->default(1);
            $table->string('brand')->nullable();
        });

        Schema::table('rlp_vendors', function (Blueprint $table) {
            $table->dropForeign(['rlp_item_id']);
            $table->dropColumn('rlp_item_id');
        });
    }
};
