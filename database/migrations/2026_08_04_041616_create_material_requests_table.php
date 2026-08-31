<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('charge_to');
            $table->integer('quantity');
            $table->string('unit');
            $table->text('description');
            $table->text('remarks')->nullable();
            $table->enum('approval_by', ['Suparlan', 'Dedi Novendri', 'Nurmila Sari M']);
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
