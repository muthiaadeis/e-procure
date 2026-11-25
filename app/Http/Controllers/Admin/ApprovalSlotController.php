<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalTemplate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalSlotController extends Controller
{
    public function index(): View
    {
        // Pengguna approver non-admin untuk pilihan penandatangan
        $users = User::where('is_admin', false)
            ->where(fn($q) => $q->where('role', 'approver')->orWhere('is_approver', true))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'jabatan']);

        if ($users->isEmpty()) {
            $users = User::where('is_admin', false)->orderBy('name')->get(['id', 'name', 'email', 'role', 'jabatan']);
        }

        $documentTypes = [
            'mr' => [
                'name' => 'Material Request (MR)',
                'code' => 'mr',
                'description' => 'Alur tanda tangan default saat dokumen Material Request baru dibuat.',
                'default_labels' => ['Reviewer', 'Approver 1', 'Finance Control'],
            ],
            'rlp' => [
                'name' => 'Rencana Pengadaan (RRP)',
                'code' => 'rlp',
                'description' => 'Alur tanda tangan default saat dokumen RRP baru diajukan.',
                'default_labels' => ['Reviewer', 'Acknowledge By', 'Approved By'],
            ],
            'po' => [
                'name' => 'Purchase Order (PO)',
                'code' => 'po',
                'description' => 'Alur tanda tangan default saat Purchase Order baru diterbitkan.',
                'default_labels' => ['Reviewer', 'Approver 1', 'Management'],
            ],
            'pr' => [
                'name' => 'Purchase Request (PR)',
                'code' => 'pr',
                'description' => 'Alur tanda tangan default saat Purchase Request baru dibuat.',
                'default_labels' => ['Reviewer', 'Approver 1', 'Management'],
            ],
        ];

        // Ambil template per tipe dokumen
        $templates = [];
        foreach (array_keys($documentTypes) as $type) {
            $templates[$type] = ApprovalTemplate::where('document_type', $type)
                ->orderBy('step')
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'step' => $t->step,
                    'label' => $t->label,
                    'user_id' => $t->user_id,
                ])->values()->all();
        }

        return view('admin.approval-slots.index', compact('users', 'documentTypes', 'templates'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validTypes = ['mr', 'rlp', 'po', 'pr'];

        $request->validate([
            'templates' => 'nullable|array',
            'templates.*' => 'nullable|array',
            'templates.*.*.label' => 'required|string|max:100',
            'templates.*.*.user_id' => 'required|exists:users,id',
        ]);

        $submittedTemplates = $request->input('templates', []);
        $typeNames = [
            'mr' => 'Material Request (MR)',
            'rlp' => 'RRP',
            'po' => 'Purchase Order (PO)',
            'pr' => 'Purchase Request (PR)',
        ];

        // Validasi: satu orang tidak boleh menandatangani dua tahap dalam alur persetujuan dokumen yang sama
        foreach ($validTypes as $type) {
            $steps = $submittedTemplates[$type] ?? [];
            $userIds = [];
            foreach ($steps as $stepData) {
                if (! empty($stepData['user_id'])) {
                    $userId = (int) $stepData['user_id'];
                    if (in_array($userId, $userIds, true)) {
                        $user = User::find($userId);
                        $userName = $user ? $user->name : "ID {$userId}";
                        $typeName = $typeNames[$type] ?? strtoupper($type);
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'templates' => "Satu orang tidak boleh menandatangani dua tahap: Pengguna {$userName} dipilih lebih dari satu kali pada alur persetujuan {$typeName}.",
                        ]);
                    }
                    $userIds[] = $userId;
                }
            }
        }

        DB::transaction(function () use ($request, $validTypes) {
            $submittedTemplates = $request->input('templates', []);

            foreach ($validTypes as $type) {
                // Hapus template lama untuk tipe dokumen ini
                ApprovalTemplate::where('document_type', $type)->delete();

                $steps = $submittedTemplates[$type] ?? [];
                $stepNumber = 2; // Step 1 selalu Prepared By dari creator

                foreach ($steps as $stepData) {
                    $label = trim($stepData['label'] ?? '');
                    $userId = !empty($stepData['user_id']) ? (int) $stepData['user_id'] : null;

                    if ($label && $userId) {
                        ApprovalTemplate::create([
                            'document_type' => $type,
                            'step' => $stepNumber++,
                            'label' => $label,
                            'user_id' => $userId,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.approval-slots.index')
            ->with('success', 'Template alur persetujuan default berhasil diperbarui.');
    }
}
