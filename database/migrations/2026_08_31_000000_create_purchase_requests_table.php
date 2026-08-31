<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('no_request')->unique(); // Request No, auto-generate (PR-YYYY-MM-XXXX)
            $table->date('date')->nullable();
            $table->string('title');
            $table->string('job_location')->nullable();
            $table->string('client')->nullable();
            $table->string('job_no')->nullable();
            $table->string('location_project')->nullable();
            $table->text('note')->nullable();

            // Ringkasan harga (dihitung dari purchase_request_items)
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->boolean('use_ppn')->default(false);
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
        Schema::dropIfExists('purchase_requests');
    }
};
