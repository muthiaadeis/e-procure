<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ApprovalTemplate extends Model
{
    protected $fillable = [
        'document_type',
        'step',
        'label',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ambil template langkah persetujuan untuk tipe dokumen tertentu.
     */
    public static function getTemplateFor(string $type): Collection
    {
        return static::where('document_type', $type)
            ->with('user')
            ->orderBy('step')
            ->get();
    }

    /**
     * Terapkan template default ke dokumen yang baru dibuat.
     * Mengisi langkah 2, 3, dst. ke tabel document_approvals.
     */
    public static function applyTemplateTo(string $type, int $documentId): void
    {
        $templates = static::where('document_type', $type)
            ->orderBy('step')
            ->get();

        if ($templates->isEmpty()) {
            return;
        }

        foreach ($templates as $tpl) {
            // Hindari duplikasi jika langkah sudah pernah ada
            DocumentApproval::firstOrCreate(
                [
                    'document_type' => $type,
                    'document_id' => $documentId,
                    'step' => $tpl->step,
                ],
                [
                    'label' => $tpl->label,
                    'user_id' => $tpl->user_id,
                ]
            );
        }
    }
}
