<?php

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
        'email',
        'password',
        'is_approver',
        'role',
        'is_admin',              // tambahkan
        'must_change_password',
    ];

    // Role User B: yang menginput / membuat Material Request
    public function isInput(): bool
    {
        return $this->role === 'input';
    }

    // Role User A: approval tahap pertama
    public function isApproverA(): bool
    {
        return $this->role === 'approver_a';
    }

    // Role User C: approval tahap kedua
    public function isApproverC(): bool
    {
        return $this->role === 'approver_c';
    }

    // Role User D: menandai status lunas / belum lunas
    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }

    // Role RRP: yang me-review RRP (tahap 1)
    public function isRlpReviewer(): bool
    {
        return $this->role === 'rlp_reviewer';
    }

    // Role RRP: yang meng-acknowledge RRP (tahap 2)
    public function isRlpAcknowledger(): bool
    {
        return $this->role === 'rlp_acknowledger';
    }

    // Role RRP: yang approve RRP (tahap akhir)
    public function isRlpApprover(): bool
    {
        return $this->role === 'rlp_approver';
    }

    public function isAdmin(): bool
    {
    return (bool) $this->is_admin;
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
            'is_admin' => 'boolean',              // tambahkan
            'must_change_password' => 'boolean',
        ];
    }
}
