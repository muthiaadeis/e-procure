<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rlp extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_rlp',
        'date',
        'part_catalog_ext_price',
        'selected_vendor_id',
        'revenue_ext_price',
        'wur_grand_total',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'part_catalog_ext_price' => 'decimal:2',
        'revenue_ext_price' => 'decimal:2',
        'wur_grand_total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(RlpItem::class);
    }

    public function vendors()
    {
        return $this->hasMany(RlpVendor::class);
    }

    public function costs()
    {
        return $this->hasMany(RlpCost::class);
    }

    public function selectedVendor()
    {
        return $this->belongsTo(RlpVendor::class, 'selected_vendor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
