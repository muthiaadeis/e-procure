<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->longText('created_signature')->nullable()->after('created_by');
            $table->longText('approved_a_signature')->nullable()->after('approved_a_at');
            $table->longText('approved_c_signature')->nullable()->after('approved_c_at');
            $table->longText('paid_signature')->nullable()->after('paid_at');
        });

        Schema::table('rlps', function (Blueprint $table) {
            $table->longText('created_signature')->nullable()->after('created_by');
            $table->longText('reviewed_signature')->nullable()->after('reviewed_at');
            $table->longText('acknowledged_signature')->nullable()->after('acknowledged_at');
            $table->longText('approved_signature')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn([
                'created_signature',
                'approved_a_signature',
                'approved_c_signature',
                'paid_signature',
            ]);
        });

        Schema::table('rlps', function (Blueprint $table) {
            $table->dropColumn([
                'created_signature',
                'reviewed_signature',
                'acknowledged_signature',
                'approved_signature',
            ]);
        });
    }
};

