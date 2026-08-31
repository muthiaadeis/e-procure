<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rlp_vendors', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('rlp_id')
                ->constrained('vendors')->nullOnDelete();
            $table->string('item_name')->nullable()->after('vendor_name');
            $table->string('brand')->nullable()->after('item_name');
            $table->string('part_number')->nullable()->after('brand');
            $table->string('delivery_estimate')->nullable()->after('ext_price');
        });
    }

    public function down(): void
    {
        Schema::table('rlp_vendors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn(['item_name', 'brand', 'part_number', 'delivery_estimate']);
        });
    }
};
