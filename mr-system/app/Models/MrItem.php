<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MrItem extends Model
{
    protected $fillable = [
        'material_request_id',
        'description',
        'quantity',
        'unit',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function materialRequest()
    {
        return $this->belongsTo(MaterialRequest::class);
    }
}
