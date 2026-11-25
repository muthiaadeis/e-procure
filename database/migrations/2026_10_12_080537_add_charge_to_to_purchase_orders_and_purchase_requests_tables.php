<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('purchase_orders') && !Schema::hasColumn('purchase_orders', 'charge_to')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->string('charge_to')->nullable()->after('client');
            });
        }

        if (Schema::hasTable('purchase_requests') && !Schema::hasColumn('purchase_requests', 'charge_to')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->string('charge_to')->nullable()->after('client');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'charge_to')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('charge_to');
            });
        }

        if (Schema::hasTable('purchase_requests') && Schema::hasColumn('purchase_requests', 'charge_to')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->dropColumn('charge_to');
            });
        }
    }
};
