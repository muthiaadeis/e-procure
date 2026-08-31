<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rlps', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'pn',
                'qty',
                'uom',
                'job_code',
                'part_catalog_u_price',
                'revenue_u_price',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('rlps', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('pn')->nullable();
            $table->integer('qty')->nullable();
            $table->string('uom')->nullable();
            $table->string('job_code')->nullable();
            $table->decimal('part_catalog_u_price', 15, 2)->default(0);
            $table->decimal('revenue_u_price', 15, 2)->default(0);
        });
    }
};
