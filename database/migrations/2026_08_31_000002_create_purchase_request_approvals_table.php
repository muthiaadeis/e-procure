<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu kotak tanda tangan. Dibuat generic (bukan kolom tetap)
        // karena beberapa stage punya lebih dari 1 orang (Review = 2 orang,
        // Acknowledge = 3 orang), jadi jumlah kotak per PR = 8 baris,
        // dibuat otomatis dari PurchaseRequest::APPROVAL_TEMPLATE saat PR dibuat.
        Schema::create('purchase_request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();

            $table->string('stage'); // prepared | review | approve | acknowledge | final_approve
            $table->string('stage_label'); // label yang tampil, mis. "Review By"
            $table->string('role_label'); // mis. "Operation Manager"
            $table->unsignedTinyInteger('sort_order'); // urutan alur tanda tangan (1..5)

            $table->foreignId('user_id')->nullable() // siapa yang tanda tangan
                ->constrained('users')->nullOnDelete();
            $table->longText('signature')->nullable(); // base64 PNG dari canvas ttd digital
            $table->timestamp('signed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_approvals');
    }
};
