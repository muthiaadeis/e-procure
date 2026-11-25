<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rlp extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_rlp',
        'date',
        'part_catalog_ext_price',
        'revenue_ext_price',
        'wur_grand_total',
        'created_by',
        'created_signature',
        'reviewed_by',
        'reviewed_at',
        'reviewed_signature',
        'acknowledged_by',
        'acknowledged_at',
        'acknowledged_signature',
        'approved_by',
        'approved_at',
        'approved_signature',
    ];

    protected $casts = [
        'date' => 'date',
        'part_catalog_ext_price' => 'decimal:2',
        'revenue_ext_price' => 'decimal:2',
        'wur_grand_total' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Generate the next auto No RRP, format: RRP001, RRP002, ...
     * Nomor berurutan 3 digit tanpa tanda hubung.
     */
    public static function generateNextNoRlp(): string
    {
        $prefix = 'RRP';

        $rlps = static::query()
            ->where(function ($q) {
                $q->where('no_rlp', 'like', 'RRP%')
                  ->orWhere('no_rlp', 'like', 'RRP-%');
            })
            ->pluck('no_rlp');

        $maxNumber = 0;
        foreach ($rlps as $no) {
            if (preg_match('/^RRP-?(\d+)$/i', trim((string) $no), $matches)) {
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
        return $this->hasMany(RlpItem::class);
    }

    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class, 'document_id')
            ->where('document_type', 'rlp')
            ->orderBy('step');
    }

    public function costs()
    {
        return $this->hasMany(RlpCost::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'rlp_id');
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'rlp_id')->latestOfMany();
    }

    // Prepared By
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Review By
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Acknowledge By
    public function acknowledger()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    // Approved By
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
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
            get: fn () => $this->approvals->isNotEmpty()
                ? $this->is_fully_signed
                : ! is_null($this->approved_at),
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

    protected function isReviewed(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->approvals->isNotEmpty()) {
                    return $this->approvals->contains(fn ($a) => $a->step > 1 && $a->isSigned());
                }

                return ! is_null($this->reviewed_at);
            },
        );
    }

    protected function isAcknowledged(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->approvals->isNotEmpty()) {
                    return $this->approvals->contains(fn ($a) => $a->step > 2 && $a->isSigned());
                }

                return ! is_null($this->acknowledged_at);
            },
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_rejected) {
                    return 'Rejected';
                }

                if ($this->approvals->isNotEmpty()) {
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

                    $current = $this->approvals->sortBy('step')->first(fn ($a) => ! $a->isSigned());
                    return $current ? 'Pending ' . $current->label : 'In Progress';
                }

                if (! $this->is_reviewed) {
                    return 'Pending Review';
                }

                if (! $this->is_acknowledged) {
                    return 'Pending Acknowledgement';
                }

                if (! $this->is_approved) {
                    return 'Pending Approval';
                }

                return 'Done';
            },
        );
    }
}
