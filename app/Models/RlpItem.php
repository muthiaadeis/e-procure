<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RlpItem extends Model
{
    protected $fillable = [
        'rlp_id',
        'job_code_id',
        'description',
        'pn',
        'qty',
        'uom',
        'part_catalog_u_price',
        'part_catalog_ext_price',
        'selected_vendor_id',
        'revenue_ext_price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'part_catalog_u_price' => 'decimal:2',
        'part_catalog_ext_price' => 'decimal:2',
        'revenue_ext_price' => 'decimal:2',
    ];

    public function rlp()
    {
        return $this->belongsTo(Rlp::class);
    }

    public function jobCode()
    {
        return $this->belongsTo(JobCode::class);
    }

    public function vendors()
    {
        return $this->hasMany(RlpVendor::class, 'rlp_item_id');
    }

    public function selectedVendor()
    {
        return $this->belongsTo(RlpVendor::class, 'selected_vendor_id');
    }
}
