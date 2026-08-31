<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage',
        'stage_label',
        'role_label',
        'sort_order',
        'user_id',
        'signature',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isSigned(): bool
    {
        return ! is_null($this->signed_at);
    }
}
