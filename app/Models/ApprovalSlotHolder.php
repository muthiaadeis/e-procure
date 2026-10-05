<?php
// app/Models/ApprovalSlotHolder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalSlotHolder extends Model
{
    protected $fillable = [
        'role_label',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Daftar posisi tanda tangan yang bisa dipegang seseorang, diambil dari template PR dan PO
    // supaya otomatis ikut kalau template berubah. Tahap "prepared" tidak dihitung karena
    // itu tanda tangan pembuat dokumen sendiri.
    public static function labels(): array
    {
        return collect(array_merge(PurchaseRequest::APPROVAL_TEMPLATE, PurchaseOrder::APPROVAL_TEMPLATE))
            ->where('stage', '!=', 'prepared')
            ->pluck('role_label')
            ->unique()
            ->values()
            ->all();
    }

    // Apakah posisi ini sudah ada pemegangnya?
    public static function hasHolders(string $roleLabel): bool
    {
        return static::where('role_label', $roleLabel)->exists();
    }
}
