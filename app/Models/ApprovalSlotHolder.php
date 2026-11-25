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

    // Posisi untuk MR dan RRP (label => dokumen). Posisi PR dan PO diambil dari template masing-masing.
    public const MR_RRP_SLOTS = [
        'MR Approval 1'   => 'MR',
        'MR Approval 2'   => 'MR',
        'MR Finance'      => 'MR',
        'RRP Review'      => 'RRP',
        'RRP Acknowledge' => 'RRP',
        'RRP Approve'     => 'RRP',
    ];

    // Daftar posisi tanda tangan yang bisa dipegang seseorang: posisi MR/RRP ditambah posisi dari
    // template PR dan PO (otomatis ikut kalau template berubah). Tahap "prepared" tidak dihitung
    // karena itu tanda tangan pembuat dokumen sendiri.
    public static function labels(): array
    {
        return array_keys(self::MR_RRP_SLOTS);
    }

    // Apakah posisi ini sudah ada pemegangnya?
    public static function hasHolders(string $roleLabel): bool
    {
        return static::where('role_label', $roleLabel)->exists();
    }
}
