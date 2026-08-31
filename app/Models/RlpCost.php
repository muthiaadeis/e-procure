<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RlpCost extends Model
{
    protected $fillable = [
        'rlp_id',
        'job_description',
        'quantity',
        'u_price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'u_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function rlp()
    {
        return $this->belongsTo(Rlp::class);
    }
}
