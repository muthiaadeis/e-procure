<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rlp_vendors', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->nullable()->after('u_price');
            $table->boolean('use_ppn')->default(false)->after('discount_percent');
        });
    }

    public function down(): void
    {
        Schema::table('rlp_vendors', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'use_ppn']);
        });
    }
};
