<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_no',
        'rlp_id',
        'our_reference',
        'supplier_no',
        'our_order_date',
        'revision',
        'to_address',
        'attn',
        'invoice_address',
        'subject',
        'project_description',
        'contact_number',
        'client',
        'charge_to',
        'final_delivery_address',
        'subtotal',
        'use_ppn',
        'ppn_percent',
        'ppn_amount',
        'grand_total',
        'created_by',
        'created_signature',
    ];

    protected $casts = [
        'our_order_date' => 'date',
        'use_ppn' => 'boolean',
        'subtotal' => 'decimal:2',
        'ppn_percent' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    /**
     * Generate the next auto PO No, format: PO001, PO002, ...
     * Nomor berurutan 3 digit tanpa tanda hubung.
     */
    public static function generateNextPoNo(): string
    {
        $prefix = 'PO';

        $pos = static::query()
            ->where(function ($q) {
                $q->where('po_no', 'like', 'PO%')
                  ->orWhere('po_no', 'like', 'PO-%');
            })
            ->pluck('po_no');

        $maxNumber = 0;
        foreach ($pos as $no) {
            if (preg_match('/^PO-?(\d+)$/i', trim((string) $no), $matches)) {
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
        return $this->hasMany(PurchaseOrderItem::class)->orderBy('line_no');
    }

    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class, 'document_id')
            ->where('document_type', 'po')
            ->orderBy('step');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rlp()
    {
        return $this->belongsTo(Rlp::class, 'rlp_id');
    }

    public function purchaseRequest()
    {
        return $this->hasOne(PurchaseRequest::class, 'purchase_order_id');
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
