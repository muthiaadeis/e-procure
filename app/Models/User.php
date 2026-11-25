<?php
// app/Models/User.php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'jabatan',
        'email',
        'password',
        'is_approver',
        'role',
        'is_admin',
        'must_change_password',
    ];

    // Staff yang menginput / membuat dokumen (MR, RRP, PO, PR).
    // Super admin BUKAN inputer dan tidak bisa menginput dokumen apapun.
    public function isInput(): bool
    {
        return ! $this->isAdmin() && $this->role === 'input';
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    // Posisi tanda tangan yang dipegang user ini (ditentukan administrator)
    public function approvalSlotHolders()
    {
        return $this->hasMany(ApprovalSlotHolder::class);
    }

    // Daftar posisi milik user ini, di-cache per request supaya tidak
    // jadi satu query per baris tabel.
    protected ?array $slotLabelsCache = null;

    protected function slotLabels(): array
    {
        return $this->slotLabelsCache ??= $this->approvalSlotHolders()->pluck('role_label')->all();
    }

    // Kalau $roleLabel kosong, artinya "memegang posisi apa saja".
    public function holdsApprovalSlot(?string $roleLabel = null): bool
    {
        $labels = $this->slotLabels();

        return $roleLabel === null ? count($labels) > 0 : in_array($roleLabel, $labels, true);
    }

    // MR
    public function isApproverA(): bool        { return $this->holdsApprovalSlot('MR Approval 1'); }
    public function isApproverC(): bool        { return $this->holdsApprovalSlot('MR Approval 2'); }
    public function isFinance(): bool          { return $this->holdsApprovalSlot('MR Finance'); }

    // RRP
    public function isRlpReviewer(): bool      { return $this->holdsApprovalSlot('RRP Review'); }
    public function isRlpAcknowledger(): bool  { return $this->holdsApprovalSlot('RRP Acknowledge'); }
    public function isRlpApprover(): bool      { return $this->holdsApprovalSlot('RRP Approve'); }

    // Memegang posisi apa saja (Super admin BUKAN approver dan tidak ikut tanda tangan)
    public function isApprover(): bool
    {
        return ! $this->isAdmin() && ($this->role === 'approver' || $this->holdsApprovalSlot());
    }

    // Cek apakah user bisa menandatangani kotak approval digital.
    // Akun admin sendiri tidak ikut tanda tangan.
    public function canSignApproval(?string $roleLabel = null): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        return $this->holdsApprovalSlot($roleLabel);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approver' => 'boolean',
            'is_admin' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }
}
