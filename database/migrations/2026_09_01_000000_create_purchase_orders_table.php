<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_no')->unique(); // Purchase Order No, auto-generate (PO-YYYY-MM-XXXX)

            // Supplier Order
            $table->string('our_reference')->nullable();
            $table->string('supplier_no')->nullable();
            $table->date('our_order_date')->nullable();
            $table->string('revision')->nullable();

            // To / Attn / Invoice Address
            $table->text('to_address')->nullable();       // "To :"
            $table->string('attn')->nullable();            // "Attn :"
            $table->text('invoice_address')->nullable();   // "Invoice Address :"

            // Subject block
            $table->string('subject')->nullable();
            $table->text('project_description')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('client')->nullable();

            // Final Delivery Address
            $table->text('final_delivery_address')->nullable();

            // Ringkasan harga (dihitung dari purchase_order_items)
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->boolean('use_ppn')->default(true);
            $table->decimal('ppn_percent', 5, 2)->default(11.00);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
