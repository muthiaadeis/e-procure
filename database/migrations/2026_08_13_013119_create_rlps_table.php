<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rlps', function (Blueprint $table) {
            $table->id();
            $table->string('no_rlp')->unique();
            $table->date('date')->nullable();
            $table->text('description');
            $table->string('pn')->nullable();
            $table->integer('qty');
            $table->string('uom');
            $table->string('job_code')->nullable();

            // Part Catalog
            $table->decimal('part_catalog_u_price', 15, 2)->default(0);
            $table->decimal('part_catalog_ext_price', 15, 2)->default(0);

            // Vendor terpilih (dipakai buat hitung revenue), FK ditambah di migration berikutnya
            $table->unsignedBigInteger('selected_vendor_id')->nullable();

            // Revenue = harga vendor terpilih - harga part catalog
            $table->decimal('revenue_u_price', 15, 2)->default(0);
            $table->decimal('revenue_ext_price', 15, 2)->default(0);

            // Total dari WUR Cost Estimasi
            $table->decimal('wur_grand_total', 15, 2)->default(0);

            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rlps');
    }
};
