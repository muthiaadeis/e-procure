<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RlpVendor extends Model
{
    protected $fillable = [
        'rlp_id',
        'vendor_id',
        'vendor_name',
        'item_name',
        'brand',
        'part_number',
        'qty',
        'u_price',
        'ext_price',
        'delivery_estimate',
    ];

    protected $casts = [
        'qty' => 'integer',
        'u_price' => 'decimal:2',
        'ext_price' => 'decimal:2',
    ];

    public function rlp()
    {
        return $this->belongsTo(Rlp::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
