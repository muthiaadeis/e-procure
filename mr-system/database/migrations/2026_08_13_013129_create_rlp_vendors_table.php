<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rlp_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rlp_id')->constrained('rlps')->cascadeOnDelete();
            $table->string('vendor_name');
            $table->decimal('u_price', 15, 2)->default(0);
            $table->decimal('ext_price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rlp_vendors');
    }
};
