<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Link Purchase Order to RRP (when generated from price comparison)
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'rlp_id')) {
                $table->foreignId('rlp_id')->nullable()
                    ->after('po_no')
                    ->constrained('rlps')
                    ->nullOnDelete();
            }
        });

        // 2. Link Purchase Request to Purchase Order (internal documentation for who the items are for)
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_requests', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')->nullable()
                    ->after('no_request')
                    ->constrained('purchase_orders')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requests', 'purchase_order_id')) {
                $table->dropConstrainedForeignId('purchase_order_id');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'rlp_id')) {
                $table->dropConstrainedForeignId('rlp_id');
            }
        });
    }
};
