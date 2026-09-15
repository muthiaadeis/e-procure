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
        'reviewed_by',
        'reviewed_at',
        'acknowledged_by',
        'acknowledged_at',
        'approved_by',
        'approved_at',
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

    public function items()
    {
        return $this->hasMany(RlpItem::class);
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

    protected function isReviewed(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->reviewed_at),
        );
    }

    protected function isAcknowledged(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->acknowledged_at),
        );
    }

    protected function isApproved(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->approved_at),
        );
    }

    // Status alur tanda tangan: Prepared -> Pending Review -> Pending Acknowledgement
    // -> Pending Approval -> Approved.
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->is_reviewed) {
                    return 'Pending Review';
                }

                if (! $this->is_acknowledged) {
                    return 'Pending Acknowledgement';
                }

                if (! $this->is_approved) {
                    return 'Pending Approval';
                }

                return 'Approved';
            },
        );
    }
}
