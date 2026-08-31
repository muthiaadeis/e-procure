<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_code',
        'vendor_name',
        'vendor_address',
        'email',
        'phone',
    ];

    /**
     * Generate kode vendor otomatis berikutnya, format: VND-0001, VND-0002, dst.
     * Diambil dari kode terakhir lalu di-increment di PHP supaya aman untuk
     * driver database apapun (mysql/sqlite).
     */
    public static function generateNextCode(): string
    {
        $lastCode = static::query()
            ->where('vendor_code', 'like', 'VND-%')
            ->orderByDesc('id')
            ->value('vendor_code');

        $lastNumber = $lastCode ? (int) substr($lastCode, 4) : 0;
        $nextNumber = $lastNumber + 1;

        return 'VND-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
