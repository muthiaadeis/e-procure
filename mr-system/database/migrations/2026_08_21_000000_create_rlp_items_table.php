<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rlp_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rlp_id')->constrained('rlps')->cascadeOnDelete();
            $table->text('description');
            $table->string('pn')->nullable();
            $table->integer('qty');
            $table->string('uom');
            $table->foreignId('job_code_id')->nullable()
                ->constrained('job_codes')->nullOnDelete();
            $table->decimal('part_catalog_u_price', 15, 2)->default(0);
            $table->decimal('part_catalog_ext_price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rlp_items');
    }
};
