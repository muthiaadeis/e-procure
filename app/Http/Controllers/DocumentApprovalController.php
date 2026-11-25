<?php

namespace App\Http\Controllers;

use App\Models\DocumentApproval;
use App\Models\MaterialRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Rlp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentApprovalController extends Controller
{
    /**
     * Super Admin mengatur siapa saja yang bertugas menandatangani dokumen ini.
     * Banyaknya step dan siapa user-nya diatur sepenuhnya oleh Super Admin.
     */
    public function store(Request $request, string $type, int $id)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403, 'Hanya Super Admin yang dapat mengatur penandatangan dokumen.');

        $validTypes = ['mr', 'rlp', 'po', 'pr'];
        abort_unless(in_array($type, $validTypes, true), 404, 'Tipe dokumen tidak valid.');

        $request->validate([
            'steps' => 'required|array|min:1',
            'steps.*.id' => 'nullable',
            'steps.*.label' => 'required|string|max:100',
            'steps.*.user_id' => 'required|exists:users,id',
        ]);

        // Validasi: satu orang tidak boleh menandatangani dua tahap pada dokumen yang sama
        $userIds = [];
        $doc = match ($type) {
            'mr' => MaterialRequest::find($id),
            'rlp' => Rlp::find($id),
            'po' => PurchaseOrder::find($id),
            'pr' => PurchaseRequest::find($id),
            default => null,
        };

        foreach ($request->steps as $stepData) {
            $userId = (int) $stepData['user_id'];
            $label = trim($stepData['label'] ?? '');

            if (in_array($userId, $userIds, true)) {
                $user = User::find($userId);
                $userName = $user ? $user->name : "ID {$userId}";
                $msg = "Satu orang tidak boleh menandatangani dua tahap: Pengguna {$userName} dipilih lebih dari satu kali.";
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->withErrors(['steps' => $msg]);
            }

            if ($doc && !empty($doc->created_by) && $label !== 'Prepared By' && $userId === (int) $doc->created_by) {
                $creator = User::find($doc->created_by);
                $creatorName = $creator ? $creator->name : "ID {$doc->created_by}";
                $msg = "Satu orang tidak boleh menandatangani dua tahap: Pembuat dokumen ({$creatorName}) tidak boleh ditugaskan pada tahap persetujuan.";
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->withErrors(['steps' => $msg]);
            }

            $userIds[] = $userId;
        }

        DB::transaction(function () use ($type, $id, $request) {
            $submittedIds = [];
            $stepNumber = 1;

            foreach ($request->steps as $stepData) {
                $stepId = !empty($stepData['id']) ? (int) $stepData['id'] : null;
                $label = trim($stepData['label']);
                $userId = (int) $stepData['user_id'];

                $approval = null;
                if ($stepId) {
                    $approval = DocumentApproval::where('document_type', $type)
                        ->where('document_id', $id)
                        ->where('id', $stepId)
                        ->first();
                }

                if ($approval) {
                    // Jika user_id diganti pada tahap yang sudah TTD, reset TTD agar approver baru dapat menandatangani
                    if ($approval->user_id !== $userId && $approval->isSigned()) {
                        $approval->signature = null;
                        $approval->signed_at = null;
                    }
                    $approval->step = $stepNumber;
                    $approval->label = $label;
                    $approval->user_id = $userId;
                    $approval->save();

                    $submittedIds[] = $approval->id;
                } else {
                    $newApproval = DocumentApproval::create([
                        'document_type' => $type,
                        'document_id' => $id,
                        'step' => $stepNumber,
                        'label' => $label,
                        'user_id' => $userId,
                    ]);
                    $submittedIds[] = $newApproval->id;
                }

                $stepNumber++;
            }

            // Hapus approvals untuk dokumen ini yang tidak ada dalam daftar yang dikirim (dihapus oleh admin)
            DocumentApproval::where('document_type', $type)
                ->where('document_id', $id)
                ->whereNotIn('id', $submittedIds)
                ->delete();

            // Sinkronisasi status legacy dokumen
            $this->syncLegacyDoc($type, $id);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Penandatangan dokumen berhasil disimpan.',
            ]);
        }

        return redirect()->back()->with('success', 'Penandatangan dokumen berhasil ditetapkan.');
    }

    /**
     * User menandatangani kotak persetujuan digital.
     * Super Admin TIDAK BISA TTD sebagai approval.
     */
    public function sign(Request $request, string $type, int $id, int $approvalId)
    {
        $user = auth()->user();
        abort_unless($user, 401);
        abort_if($user->isAdmin(), 403, 'Super Admin tidak dapat menandatangani dokumen sebagai approval.');

        $approval = DocumentApproval::where('document_type', $type)
            ->where('document_id', $id)
            ->findOrFail($approvalId);

        abort_unless(
            $approval->canBeSignedBy($user),
            403,
            'Anda tidak memiliki akses untuk menandatangani kotak persetujuan ini atau tahap sebelumnya belum selesai.'
        );

        $validated = $request->validate([
            'signature' => 'required|string',
        ]);

        $approval->update([
            'signature' => $validated['signature'],
            'signed_at' => now(),
        ]);

        // Sinkronisasi status/flag legacy jika ada
        $this->syncLegacyDoc($type, $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tanda tangan berhasil dibubuhkan.',
            ]);
        }

        return redirect()->back()->with('success', "Tanda tangan {$approval->label} berhasil dibubuhkan.");
    }

    /**
     * User menolak persetujuan dokumen dengan alasan.
     */
    public function reject(Request $request, string $type, int $id, int $approvalId)
    {
        $user = auth()->user();
        abort_unless($user, 401);
        abort_if($user->isAdmin(), 403, 'Super Admin tidak dapat menolak dokumen sebagai approval.');

        $approval = DocumentApproval::where('document_type', $type)
            ->where('document_id', $id)
            ->findOrFail($approvalId);

        abort_unless(
            $approval->canBeRejectedBy($user),
            403,
            'Anda tidak memiliki akses untuk menolak dokumen ini atau tahap sebelumnya belum selesai.'
        );

        $reason = $request->input('reason') ?? $request->input('rejection_reason');
        if (! $reason) {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);
            $reason = $validated['reason'];
        }

        $approval->update([
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Sinkronisasi status/flag legacy jika ada
        $this->syncLegacyDocRejection($type, $id, $approval, $reason);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil ditolak.',
            ]);
        }

        return redirect()->back()->with('success', "Dokumen berhasil ditolak pada tahap {$approval->label}.");
    }

    /**
     * Sinkronisasi ke model dokumen agar status/tampilan lama tetap kompatibel.
     */
    private function syncLegacyDoc(string $type, int $id): void
    {
        if ($type === 'mr') {
            $doc = MaterialRequest::find($id);
            if ($doc) {
                $approvals = $doc->approvals()->get();
                $isAllSigned = $approvals->isNotEmpty()
                    && $approvals->contains(fn ($a) => $a->step > 1)
                    && $approvals->every(fn ($a) => $a->isSigned());
                if ($isAllSigned) {
                    $doc->update([
                        'approved_a_at' => $doc->approved_a_at ?? now(),
                        'approved_c_at' => $doc->approved_c_at ?? now(),
                        'paid_at' => $doc->paid_at ?? now(),
                    ]);
                }
            }
        } elseif ($type === 'rlp') {
            $doc = Rlp::find($id);
            if ($doc) {
                $approvals = $doc->approvals()->get();
                $isAllSigned = $approvals->isNotEmpty()
                    && $approvals->contains(fn ($a) => $a->step > 1)
                    && $approvals->every(fn ($a) => $a->isSigned());
                if ($isAllSigned) {
                    $doc->update([
                        'reviewed_at' => $doc->reviewed_at ?? now(),
                        'acknowledged_at' => $doc->acknowledged_at ?? now(),
                        'approved_at' => $doc->approved_at ?? now(),
                    ]);
                }
            }
        }
    }

    /**
     * Sinkronisasi penolakan legacy jika dokumen adalah MR.
     */
    private function syncLegacyDocRejection(string $type, int $id, DocumentApproval $approval, string $reason): void
    {
        if ($type === 'mr') {
            $doc = MaterialRequest::find($id);
            if ($doc) {
                if ($approval->step === 1) {
                    $doc->update([
                        'rejected_a_by' => $approval->user_id,
                        'rejected_a_at' => now(),
                        'rejection_a_reason' => $reason,
                    ]);
                } else {
                    $doc->update([
                        'rejected_c_by' => $approval->user_id,
                        'rejected_c_at' => now(),
                        'rejection_c_reason' => $reason,
                    ]);
                }
                $doc->touch();
            }
        } else {
            $doc = match ($type) {
                'rlp' => Rlp::find($id),
                'po' => PurchaseOrder::find($id),
                'pr' => PurchaseRequest::find($id),
                default => null,
            };
            if ($doc) {
                $doc->touch();
            }
        }
    }
}

