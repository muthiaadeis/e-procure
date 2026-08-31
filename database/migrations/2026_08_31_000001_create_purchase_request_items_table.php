<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->unsignedInteger('line_no'); // kolom "No"
            $table->text('description');

            // Line Item terdiri dari 2 tingkat: Tabel & Sub Tabel
            $table->string('line_table')->nullable();     // Tabel
            $table->string('line_sub_table')->nullable();  // Sub Tabel

            $table->decimal('qty', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0); // qty x price
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
