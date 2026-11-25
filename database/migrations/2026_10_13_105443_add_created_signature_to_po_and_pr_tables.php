<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->longText('created_signature')->nullable()->after('created_by');
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->longText('created_signature')->nullable()->after('created_by');
        });

        // Backfill: Masukkan Prepared By sebagai Tahap 1 untuk dokumen yang sudah ada
        $types = [
            'mr' => \App\Models\MaterialRequest::all(),
            'rlp' => \App\Models\Rlp::all(),
            'po' => \App\Models\PurchaseOrder::all(),
            'pr' => \App\Models\PurchaseRequest::all(),
        ];

        foreach ($types as $type => $docs) {
            foreach ($docs as $doc) {
                $hasPreparedBy = \App\Models\DocumentApproval::where('document_type', $type)
                    ->where('document_id', $doc->id)
                    ->where('label', 'Prepared By')
                    ->exists();

                if (! $hasPreparedBy && $doc->created_by) {
                    // Jika sudah ada step lain yang terdaftar, geser nomor stepnya +1
                    \App\Models\DocumentApproval::where('document_type', $type)
                        ->where('document_id', $doc->id)
                        ->increment('step');

                    \App\Models\DocumentApproval::create([
                        'document_type' => $type,
                        'document_id' => $doc->id,
                        'step' => 1,
                        'label' => 'Prepared By',
                        'user_id' => $doc->created_by,
                        'signature' => $doc->created_signature ?? null,
                        'signed_at' => $doc->created_at ?? now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('created_signature');
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('created_signature');
        });
    }
};
