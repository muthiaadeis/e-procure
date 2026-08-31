<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->unsignedInteger('line_no'); // kolom "No"
            $table->text('description'); // bisa berisi beberapa baris deskripsi dalam 1 row

            $table->decimal('qty', 12, 2)->default(0);
            $table->string('uom')->nullable();      // Unit of Measure
            $table->string('brand')->nullable();
            $table->decimal('price', 15, 2)->default(0);       // Unit Price
            $table->decimal('total_price', 15, 2)->default(0); // qty x price

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
