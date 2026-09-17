<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    use HasFactory;

    // Batas waktu (hari) sebelum dianggap terlambat
    // Approval 1 & Approval 2 masing-masing 2 hari (dihitung dari saat MR itu "masuk" ke tahap tsb),
    // sedangkan finance (pembayaran) 3 hari sejak approval C selesai.
    const APPROVAL_1_DEADLINE_DAYS = 2;
    const APPROVAL_2_DEADLINE_DAYS = 2;
    const PAYMENT_DEADLINE_DAYS = 3;

        /**
     * Generate the next auto No MR, format: MR-{YYYY}-{MM}-{seq}, e.g. MR-2026-08-0001.
     * The sequence resets every month and is based on how many MRs already
     * exist for the current year+month.
     */
    public static function generateNextNoMr(): string
    {
        $prefix = 'MR-' . now()->format('Y-m') . '-';

        $lastNoMr = static::query()
            ->where('no_mr', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('no_mr');

        $lastNumber = $lastNoMr ? (int) substr($lastNoMr, strlen($prefix)) : 0;
        $nextNumber = $lastNumber + 1;

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'no_mr',
        'created_by',
        'created_signature',
        'date',
        'charge_to',
        'approved_a_by',
        'approved_a_at',
        'approved_a_signature',
        'rejected_a_by',
        'rejected_a_at',
        'rejection_a_reason',
        'approved_c_by',
        'approved_c_at',
        'approved_c_signature',
        'rejected_c_by',
        'rejected_c_at',
        'rejection_c_reason',
        'paid_by',
        'paid_at',
        'paid_signature',
        'finance_rejected_by',
        'finance_rejected_at',
        'finance_rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_a_at' => 'datetime',
        'rejected_a_at' => 'datetime',
        'approved_c_at' => 'datetime',
        'rejected_c_at' => 'datetime',
        'paid_at' => 'date',
        'finance_rejected_at' => 'datetime',
    ];

    // Item-item MR (description, quantity, unit) — satu MR bisa banyak item
    public function items()
    {
        return $this->hasMany(MrItem::class)->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // User A yang approve tahap pertama
    public function approverA()
    {
        return $this->belongsTo(User::class, 'approved_a_by');
    }

    // User yang menolak tahap pertama
    public function rejectorA()
    {
        return $this->belongsTo(User::class, 'rejected_a_by');
    }

    // User C yang approve tahap kedua
    public function approverC()
    {
        return $this->belongsTo(User::class, 'approved_c_by');
    }

    // User yang menolak tahap kedua
    public function rejectorC()
    {
        return $this->belongsTo(User::class, 'rejected_c_by');
    }

    // User D yang menandai lunas
    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // User finance yang menolak
    public function financeRejector()
    {
        return $this->belongsTo(User::class, 'finance_rejected_by');
    }

    protected function isApprovedByA(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->approved_a_at),
        );
    }

    protected function isRejectedByA(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->rejected_a_at),
        );
    }

    protected function isApprovedByC(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->approved_c_at),
        );
    }

    protected function isRejectedByC(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->rejected_c_at),
        );
    }

    protected function isRejectedByFinance(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->finance_rejected_at),
        );
    }

    // Ditolak di tahap manapun (Approval 1, Approval 2, atau Finance)
    protected function isRejected(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_rejected_by_a || $this->is_rejected_by_c || $this->is_rejected_by_finance,
        );
    }

    // Dianggap "approved" sepenuhnya kalau sudah lewat approval A dan C
    protected function isApproved(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_approved_by_a && $this->is_approved_by_c,
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_rejected_by_a) {
                    return 'Rejected (Approval 1)';
                }

                if (! $this->is_approved_by_a) {
                    return 'Pending Approval 1';
                }

                if ($this->is_rejected_by_c) {
                    return 'Rejected (Approval 2)';
                }

                if (! $this->is_approved_by_c) {
                    return 'Pending Approval 2';
                }

                if ($this->is_rejected_by_finance) {
                    return 'Rejected (Finance)';
                }

                return $this->paid_at ? 'Done' : 'Pending Payment';
            },
        );
    }

    // Terlambat Approval 1: sudah lebih dari 2 hari sejak tanggal MR diinput,
    // tapi User A belum approve.
    protected function isOverdueApproval1(): Attribute
    {
        return Attribute::make(
            get: fn () => ! $this->is_approved_by_a
                && ! $this->is_rejected_by_a
                && $this->date
                && $this->date->copy()->addDays(self::APPROVAL_1_DEADLINE_DAYS)->isPast(),
        );
    }

    // Terlambat Approval 2: sudah lebih dari 2 hari sejak Approval 1 selesai,
    // tapi User C belum approve.
    protected function isOverdueApproval2(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_approved_by_a
                && ! $this->is_approved_by_c
                && ! $this->is_rejected_by_c
                && $this->approved_a_at
                && $this->approved_a_at->copy()->addDays(self::APPROVAL_2_DEADLINE_DAYS)->isPast(),
        );
    }

    // Gabungan dua tahap approval, dipakai internal buat overdue umum
    protected function isOverdueApproval(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_overdue_approval_1 || $this->is_overdue_approval_2,
        );
    }

    // Terlambat pembayaran (finance): sudah lebih dari 3 hari sejak approval C selesai,
    // tapi User D belum menandai lunas.
    protected function isOverduePayment(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_approved
                && ! $this->is_rejected_by_finance
                && is_null($this->paid_at)
                && $this->approved_c_at
                && $this->approved_c_at->copy()->addDays(self::PAYMENT_DEADLINE_DAYS)->isPast(),
        );
    }

    // Batas waktu Approval 1: 2 hari sejak tanggal MR diinput
    protected function approval1DeadlineDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date
                ? $this->date->copy()->addDays(self::APPROVAL_1_DEADLINE_DAYS)
                : null,
        );
    }

    // Batas waktu Approval 2: 2 hari sejak Approval 1 selesai
    protected function approval2DeadlineDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approved_a_at
                ? $this->approved_a_at->copy()->addDays(self::APPROVAL_2_DEADLINE_DAYS)
                : null,
        );
    }

    // Batas waktu pembayaran (finance): 3 hari sejak approval C selesai
    protected function paymentDeadlineDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approved_c_at
                ? $this->approved_c_at->copy()->addDays(self::PAYMENT_DEADLINE_DAYS)
                : null,
        );
    }

    // Deadline yang lagi relevan buat ditampilkan di tabel, sesuai status saat ini:
    // Menunggu Approval 1 -> deadline approval 1, Menunggu Approval 2 -> deadline approval 2,
    // Menunggu Pembayaran -> deadline finance, Done -> gak ada deadline lagi.
    protected function deadlineDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->is_approved_by_a) {
                    return $this->approval_1_deadline_date;
                }

                if (! $this->is_approved_by_c) {
                    return $this->approval_2_deadline_date;
                }

                if (is_null($this->paid_at)) {
                    return $this->payment_deadline_date;
                }

                return null;
            },
        );
    }

    // Gabungan, dipakai buat nampilin badge warning di tabel
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_overdue_approval_1 || $this->is_overdue_approval_2 || $this->is_overdue_payment,
        );
    }

    // Label alasan overdue, buat ditampilkan di UI
    protected function overdueReason(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_overdue_approval_1) {
                    return 'Approval 1 overdue (more than '.self::APPROVAL_1_DEADLINE_DAYS.' days since MR date)';
                }

                if ($this->is_overdue_approval_2) {
                    return 'Approval 2 overdue (more than '.self::APPROVAL_2_DEADLINE_DAYS.' days since Approval 1)';
                }

                if ($this->is_overdue_payment) {
                    return 'Payment overdue (more than '.self::PAYMENT_DEADLINE_DAYS.' days since approval)';
                }

                return null;
            },
        );
    }
}
