<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RlpVendor extends Model
{
    protected $fillable = [
        'rlp_item_id',
        'vendor_id',
        'vendor_name',
        'u_price',
        'ext_price',
        'delivery_estimate',
        'discount_percent',
        'use_ppn',
    ];

    protected $casts = [
        'u_price' => 'decimal:2',
        'ext_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'use_ppn' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(RlpItem::class, 'rlp_item_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
