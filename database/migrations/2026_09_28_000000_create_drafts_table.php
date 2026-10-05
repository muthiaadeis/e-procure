<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('form', 50);
            $table->string('context', 100)->default('');
            $table->longText('payload');
            $table->timestamps();

            $table->unique(['user_id', 'form', 'context']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drafts');
    }
};
