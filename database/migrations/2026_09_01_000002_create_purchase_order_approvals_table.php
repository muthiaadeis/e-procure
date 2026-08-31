<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu kotak tanda tangan, dibuat otomatis dari
        // PurchaseOrder::APPROVAL_TEMPLATE saat PO pertama dibuat.
        Schema::create('purchase_order_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();

            $table->string('stage'); // prepared | review | approve | knowledge | final_approve
            $table->string('stage_label'); // label yang tampil, mis. "Knowledge By"
            $table->string('role_label');  // mis. "Finance Control"
            $table->unsignedTinyInteger('sort_order'); // urutan alur tanda tangan (1..5)

            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->longText('signature')->nullable();
            $table->timestamp('signed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_approvals');
    }
};
