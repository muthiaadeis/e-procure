<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_request',
        'date',
        'title',
        'job_location',
        'client',
        'job_no',
        'location_project',
        'note',
        'subtotal',
        'use_ppn',
        'ppn_percent',
        'ppn_amount',
        'grand_total',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'use_ppn' => 'boolean',
        'subtotal' => 'decimal:2',
        'ppn_percent' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    // Template alur tanda tangan PR. Urutan (sort_order) menentukan siapa
    // harus tanda tangan duluan sebelum tahap berikutnya bisa dibuka.
    // Silakan sesuaikan role_label di sini kalau ada perubahan jabatan.
    public const APPROVAL_TEMPLATE = [
        ['stage' => 'prepared',      'stage_label' => 'Prepared By',   'role_label' => 'Admin Support',        'sort_order' => 1],
        ['stage' => 'review',        'stage_label' => 'Review By',     'role_label' => 'Operation Manager',    'sort_order' => 2],
        ['stage' => 'review',        'stage_label' => 'Review By',     'role_label' => 'Project Support',      'sort_order' => 2],
        ['stage' => 'approve',       'stage_label' => 'Approve',       'role_label' => 'KSO Representative',   'sort_order' => 3],
        ['stage' => 'acknowledge',   'stage_label' => 'Acknowledge',   'role_label' => 'Finance Control',      'sort_order' => 4],
        ['stage' => 'acknowledge',   'stage_label' => 'Acknowledge',   'role_label' => 'General Manager',      'sort_order' => 4],
        ['stage' => 'acknowledge',   'stage_label' => 'Acknowledge',   'role_label' => 'VIP Director',         'sort_order' => 4],
        ['stage' => 'final_approve', 'stage_label' => 'Final Approve', 'role_label' => 'Director',             'sort_order' => 5],
    ];

    public static function generateNextNoRequest(): string
    {
        $prefix = 'PR-' . now()->format('Y-m') . '-';

        $lastNo = static::query()
            ->where('no_request', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('no_request');

        $lastNumber = $lastNo ? (int) substr($lastNo, strlen($prefix)) : 0;
        $nextNumber = $lastNumber + 1;

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class)->orderBy('line_no');
    }

    public function approvals()
    {
        return $this->hasMany(PurchaseRequestApproval::class)->orderBy('sort_order')->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function isFullySigned(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approvals->isNotEmpty() && $this->approvals->every(fn ($a) => ! is_null($a->signed_at)),
        );
    }

    protected function isDraft(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approvals->every(fn ($a) => is_null($a->signed_at)),
        );
    }

    // Draft (belum ada ttd sama sekali) -> In Progress -> Completed (semua kotak sudah ttd)
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_fully_signed) {
                    return 'Completed';
                }

                if ($this->is_draft) {
                    return 'Draft';
                }

                return 'In Progress';
            },
        );
    }
}
