<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_request',
        'purchase_order_id',
        'date',
        'title',
        'job_location',
        'client',
        'charge_to',
        'job_no',
        'location_project',
        'note',
        'subtotal',
        'use_ppn',
        'ppn_percent',
        'ppn_amount',
        'grand_total',
        'created_by',
        'created_signature',
    ];

    protected $casts = [
        'date' => 'date',
        'use_ppn' => 'boolean',
        'subtotal' => 'decimal:2',
        'ppn_percent' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    /**
     * Generate the next auto No Request, format: PR001, PR002, ...
     * Nomor berurutan 3 digit tanpa tanda hubung.
     */
    public static function generateNextNoRequest(): string
    {
        $prefix = 'PR';

        $prs = static::query()
            ->where(function ($q) {
                $q->where('no_request', 'like', 'PR%')
                  ->orWhere('no_request', 'like', 'PR-%');
            })
            ->pluck('no_request');

        $maxNumber = 0;
        foreach ($prs as $no) {
            if (preg_match('/^PR-?(\d+)$/i', trim((string) $no), $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        $nextNumber = $maxNumber + 1;

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class)->orderBy('line_no');
    }

    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class, 'document_id')
            ->where('document_type', 'pr')
            ->orderBy('step');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    protected function isFullySigned(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approvals->isNotEmpty()
                && $this->approvals->contains(fn ($a) => $a->step > 1)
                && $this->approvals->every(fn ($a) => ! is_null($a->signed_at)),
        );
    }

    protected function isApproved(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_fully_signed,
        );
    }

    protected function isDraft(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approvals->every(fn ($a) => is_null($a->signed_at)),
        );
    }

    protected function isRejected(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approvals->isNotEmpty() && $this->approvals->contains(fn ($a) => $a->isRejected()),
        );
    }

    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_rejected || $this->status === 'Done') {
                    return false;
                }

                return $this->approvals->isNotEmpty() && $this->approvals->contains(fn ($a) => $a->isOverdue());
            },
        );
    }

    // Draft (belum ada ttd sama sekali) -> Pending Setup -> In Progress / Pending Approver -> Done (semua kotak sudah ttd) / Rejected
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_rejected) {
                    return 'Rejected';
                }

                $hasConfiguredApprovals = $this->approvals->contains(fn ($a) => $a->step > 1);
                if (! $hasConfiguredApprovals) {
                    return 'Pending Setup';
                }

                if ($this->is_fully_signed) {
                    return 'Done';
                }

                if ($this->is_draft) {
                    return 'Draft';
                }

                $currentApproval = $this->approvals->sortBy('step')->first(fn ($a) => ! $a->isSigned());
                return $currentApproval ? 'Pending ' . $currentApproval->label : 'In Progress';
            },
        );
    }
}
