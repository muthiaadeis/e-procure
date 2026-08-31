<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RlpItem extends Model
{
    protected $fillable = [
        'rlp_id',
        'description',
        'pn',
        'qty',
        'uom',
        'job_code_id',
        'part_catalog_u_price',
        'part_catalog_ext_price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'part_catalog_u_price' => 'decimal:2',
        'part_catalog_ext_price' => 'decimal:2',
    ];

    public function rlp()
    {
        return $this->belongsTo(Rlp::class);
    }

    public function jobCode()
    {
        return $this->belongsTo(JobCode::class);
    }
}
