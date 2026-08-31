<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_code',
        'description',
        'price',
        'part_number',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }
}
