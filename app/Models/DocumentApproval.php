<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApproval extends Model
{
    use HasFactory;

    public const DEADLINE_DAYS = 2; // Batas waktu seragam 2 hari per tahap approval

    protected $fillable = [
        'document_type',
        'document_id',
        'step',
        'label',
        'user_id',
        'signature',
        'signed_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'step' => 'integer',
        'signed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->label;
    }

    public function getStageLabelAttribute(): string
    {
        return $this->label;
    }

    public function getSortOrderAttribute(): int
    {
        return $this->step;
    }

    public function isSigned(): bool
    {
        return ! is_null($this->signed_at) && ! empty($this->signature);
    }

    public function isRejected(): bool
    {
        return ! is_null($this->rejected_at);
    }

    /**
     * Waktu ketika tahap ini mulai aktif / terbuka untuk diproses.
     */
    public function unlockedAt(): Carbon
    {
        $myCreatedAt = $this->created_at ?? now();

        if ($this->step <= 1) {
            return $myCreatedAt;
        }

        // Ambil step sebelumnya pada dokumen yang sama
        $prev = static::where('document_type', $this->document_type)
            ->where('document_id', $this->document_id)
            ->where('step', '<', $this->step)
            ->orderByDesc('step')
            ->first();

        if (! $prev) {
            return $myCreatedAt;
        }

        // Jika tahap sebelumnya sudah bertanda tangan, waktu mulai adalah tanggal TTD prev
        if ($prev->signed_at) {
            $prevDoneTime = $prev->signed_at;
        } else {
            // Jika prev belum TTD, waktu mulai adalah saat prev jatuh tempo
            $prevDoneTime = $prev->deadlineDate();
        }

        // Batas waktu tidak boleh dihitung sebelum tahap ini dibuat oleh admin
        if ($myCreatedAt->gt($prevDoneTime)) {
            return $myCreatedAt;
        }

        return $prevDoneTime;
    }

    /**
     * Hitung tanggal deadline untuk tahap ini (2 hari per tahap).
     */
    public function deadlineDate(): Carbon
    {
        return $this->unlockedAt()->copy()->addDays(self::DEADLINE_DAYS);
    }

    /**
     * Apakah tahap ini telah melewati batas waktu 2 hari dan belum ditandatangani?
     */
    public function isOverdue(): bool
    {
        if ($this->isSigned() || $this->isRejected()) {
            return false;
        }

        return $this->isUnlocked() && $this->deadlineDate()->isPast();
    }

    /**
     * Apakah tahap ini sudah terbuka untuk ditandatangani?
     * Terbuka jika tahap sebelumnya sudah ditandatangani ATAU tahap sebelumnya sudah overdue (> 2 hari),
     * dan tidak ada tahap yang ditolak pada dokumen ini.
     */
    public function isUnlocked(): bool
    {
        // Jika dokumen ini sendiri sudah ditolak di tahap manapun, kunci semua yang belum signed
        $hasAnyRejection = static::where('document_type', $this->document_type)
            ->where('document_id', $this->document_id)
            ->whereNotNull('rejected_at')
            ->exists();

        if ($hasAnyRejection) {
            return false;
        }

        if ($this->step <= 1) {
            return true;
        }

        $prev = static::where('document_type', $this->document_type)
            ->where('document_id', $this->document_id)
            ->where('step', '<', $this->step)
            ->orderByDesc('step')
            ->first();

        if (! $prev) {
            return true;
        }

        if ($prev->isRejected()) {
            return false;
        }

        // Jika tahap sebelumnya sudah TTD atau sudah lewat 2 hari (overdue), tahap ini otomatis terbuka!
        return $prev->isSigned() || $prev->isOverdue();
    }

    /**
     * Cek apakah user saat ini berhak menandatangani atau menolak kotak ini.
     * Aturan:
     * 1. Super Admin TIDAK BISA TTD / Reject sebagai approval.
     * 2. Hanya user yang ditugaskan (assigned user) yang bisa TTD / Reject.
     * 3. Kotak belum ditandatangani dan belum ditolak.
     * 4. Tahap sudah terbuka (unlocked).
     */
    public function canBeSignedBy(?User $user): bool
    {
        if (! $user || $user->isAdmin()) {
            return false;
        }

        if ($user->id !== $this->user_id) {
            return false;
        }

        if ($this->isSigned() || $this->isRejected() || ! $this->isUnlocked()) {
            return false;
        }

        // Validasi: satu orang tidak boleh menandatangani dua tahap pada dokumen yang sama
        // 1. Cek apakah user sudah menandatangani tahap lain pada dokumen ini
        $alreadySignedOtherStep = static::where('document_type', $this->document_type)
            ->where('document_id', $this->document_id)
            ->where('id', '!=', $this->id)
            ->where('user_id', $user->id)
            ->whereNotNull('signed_at')
            ->exists();

        if ($alreadySignedOtherStep) {
            return false;
        }

        // 2. Pembuat dokumen (yang sudah menandatangani Step 1 Prepared By) tidak boleh menandatangani tahap persetujuan berikutnya (Step > 1)
        if ($this->step > 1 && $this->document && $this->document->created_by === $user->id) {
            return false;
        }

        return true;
    }

    public function canBeRejectedBy(?User $user): bool
    {
        return $this->canBeSignedBy($user);
    }

    public function getDocumentUrlAttribute(): string
    {
        return match ($this->document_type) {
            'mr' => route('material-requests.show', $this->document_id),
            'rlp' => route('rlps.show', $this->document_id),
            'po' => route('purchase-orders.show', $this->document_id),
            'pr' => route('purchase-requests.show', $this->document_id),
            default => '#',
        };
    }

    public function getDocumentModuleAttribute(): string
    {
        return match ($this->document_type) {
            'mr' => 'MR',
            'rlp' => 'RRP',
            'po' => 'PO',
            'pr' => 'PR',
            default => strtoupper($this->document_type),
        };
    }

    public function getDocumentBadgeClassAttribute(): string
    {
        return match ($this->document_type) {
            'mr' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'rlp' => 'bg-purple-50 text-purple-700 border-purple-200',
            'po' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'pr' => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }

    public function getDocumentAttribute()
    {
        return match ($this->document_type) {
            'mr' => MaterialRequest::find($this->document_id),
            'rlp' => Rlp::find($this->document_id),
            'po' => PurchaseOrder::find($this->document_id),
            'pr' => PurchaseRequest::find($this->document_id),
            default => null,
        };
    }

    public function getDocumentNumberAttribute(): string
    {
        $doc = $this->document;
        if (! $doc) {
            return $this->document_module . ' #' . $this->document_id;
        }

        return match ($this->document_type) {
            'mr' => $doc->no_mr ?? ('MR #' . $this->document_id),
            'rlp' => $doc->no_rlp ?? ('RRP #' . $this->document_id),
            'po' => $doc->po_no ?? ('PO #' . $this->document_id),
            'pr' => $doc->no_request ?? ('PR #' . $this->document_id),
            default => $this->document_module . ' #' . $this->document_id,
        };
    }
}
