<?php
// app/Http/Controllers/Admin/ApprovalSlotController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalSlotHolder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApprovalSlotController extends Controller
{
    public function index(): View
    {
        $labels = ApprovalSlotHolder::labels();

        // Akun admin tidak ikut tanda tangan dokumen, jadi tidak muncul di daftar pemegang.
        $users = User::where('is_admin', false)->orderBy('name')->get(['id', 'name', 'email', 'role']);

        // [role_label => [user_id, user_id, ...]]
        $holders = ApprovalSlotHolder::all()
            ->groupBy('role_label')
            ->map(fn ($group) => $group->pluck('user_id')->all());

        // Posisi ini dipakai di dokumen apa saja (PR / PO)
        $usage = [];
        foreach (['PR' => PurchaseRequest::APPROVAL_TEMPLATE, 'PO' => PurchaseOrder::APPROVAL_TEMPLATE] as $doc => $template) {
            foreach ($template as $row) {
                if ($row['stage'] !== 'prepared') {
                    $usage[$row['role_label']][$doc] = $doc;
                }
            }
        }

        return view('admin.approval-slots.index', compact('labels', 'users', 'holders', 'usage'));
    }

    public function update(Request $request): RedirectResponse
    {
        $labels = ApprovalSlotHolder::labels();

        $validated = $request->validate([
            'slots' => ['nullable', 'array'],
            'slots.*.label' => ['required', 'string', Rule::in($labels)],
            'slots.*.users' => ['nullable', 'array'],
            'slots.*.users.*' => ['integer', Rule::exists('users', 'id')->where('is_admin', false)],
        ]);

        DB::transaction(function () use ($validated, $labels) {
            // Ganti semua pemegang untuk posisi yang dikelola halaman ini
            ApprovalSlotHolder::whereIn('role_label', $labels)->delete();

            foreach ($validated['slots'] ?? [] as $slot) {
                foreach (array_unique($slot['users'] ?? []) as $userId) {
                    ApprovalSlotHolder::create([
                        'role_label' => $slot['label'],
                        'user_id' => $userId,
                    ]);
                }
            }
        });

        return redirect()->route('admin.approval-slots.index')
            ->with('success', 'Approval positions updated successfully.');
    }
}
