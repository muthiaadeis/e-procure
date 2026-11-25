<?php

namespace App\Http\Controllers;

use App\Models\DocumentApproval;
use App\Models\MaterialRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Rlp;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([
                'query' => $q,
                'results' => [],
                'total' => 0,
            ]);
        }

        $results = [];

        // 1. Material Requests
        $mrs = MaterialRequest::where('no_mr', 'like', "%{$q}%")
            ->orWhere('charge_to', 'like', "%{$q}%")
            ->latest('created_at')
            ->take(4)
            ->get();
        foreach ($mrs as $mr) {
            $results[] = [
                'type' => 'Material Request',
                'badge' => 'MR',
                'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'title' => $mr->no_mr ?? 'MR-' . $mr->id,
                'subtitle' => $mr->charge_to ?? 'Material Request',
                'status' => $mr->status ?? 'Active',
                'url' => route('material-requests.show', $mr),
            ];
        }

        // 2. RLP
        $rlps = Rlp::where('no_rlp', 'like', "%{$q}%")
            ->latest('created_at')
            ->take(4)
            ->get();
        foreach ($rlps as $rlp) {
            $results[] = [
                'type' => 'Local Purchase',
                'badge' => 'RLP',
                'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                'title' => $rlp->no_rlp ?? 'RLP-' . $rlp->id,
                'subtitle' => 'Local Purchase Request',
                'status' => $rlp->approved_by ? 'Approved' : 'Pending',
                'url' => route('rlps.show', $rlp),
            ];
        }

        // 3. Purchase Request
        $prs = PurchaseRequest::where('no_request', 'like', "%{$q}%")
            ->orWhere('title', 'like', "%{$q}%")
            ->orWhere('client', 'like', "%{$q}%")
            ->latest('created_at')
            ->take(4)
            ->get();
        foreach ($prs as $pr) {
            $results[] = [
                'type' => 'Purchase Request',
                'badge' => 'PR',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'title' => $pr->no_request ?? 'PR-' . $pr->id,
                'subtitle' => $pr->title ?? ($pr->client ?? 'Purchase Request'),
                'status' => 'Pending',
                'url' => route('purchase-requests.show', $pr),
            ];
        }

        // 4. Purchase Order
        $pos = PurchaseOrder::where('po_no', 'like', "%{$q}%")
            ->orWhere('project_description', 'like', "%{$q}%")
            ->orWhere('client', 'like', "%{$q}%")
            ->orWhere('our_reference', 'like', "%{$q}%")
            ->latest('created_at')
            ->take(4)
            ->get();
        foreach ($pos as $po) {
            $results[] = [
                'type' => 'Purchase Order',
                'badge' => 'PO',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'title' => $po->po_no ?? 'PO-' . $po->id,
                'subtitle' => $po->project_description ?? ($po->client ?? 'Purchase Order'),
                'status' => 'Active',
                'url' => route('purchase-orders.show', $po),
            ];
        }

        // 5. Vendor
        $vendors = Vendor::where('vendor_name', 'like', "%{$q}%")
            ->orWhere('vendor_code', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->latest('id')
            ->take(3)
            ->get();
        foreach ($vendors as $vendor) {
            $results[] = [
                'type' => 'Vendor',
                'badge' => 'VDR',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
                'title' => $vendor->vendor_name,
                'subtitle' => ($vendor->vendor_code ? $vendor->vendor_code . ' • ' : '') . ($vendor->email ?? ($vendor->phone ?? 'Active Vendor')),
                'status' => 'Master Data',
                'url' => route('vendors.index'),
            ];
        }

        return response()->json([
            'query' => $q,
            'results' => $results,
            'total' => count($results),
        ]);
    }

    public function notifications()
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
            ]);
        }

        $notifications = [];

        if ($user->isAdmin()) {
            // ==========================================
            // SUPER ADMIN NOTIFICATIONS
            // 1. Dokumen Baru (Baik yang belum atau sudah memiliki alur approval)
            // ==========================================
            $recentMrs = MaterialRequest::with(['creator', 'approvals'])->latest('created_at')->take(5)->get();
            foreach ($recentMrs as $mr) {
                $hasApprover = $mr->approvals->contains(fn($q) => $q->step > 1);
                $isDone = ($mr->status === 'Done');
                $isRejected = str_starts_with($mr->status ?? '', 'Rejected');
                if ($isDone || $isRejected) continue;

                if (! $hasApprover) {
                    $notifications[] = [
                        'id' => 'admin_unassigned_mr_' . $mr->id,
                        'module' => 'MR',
                        'module_badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'icon_bg' => 'bg-purple-50 text-purple-600',
                        'icon_type' => 'document',
                        'status_label' => 'Perlu Approver',
                        'status_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'title' => 'Atur Approver: ' . ($mr->no_mr ?? 'MR-' . $mr->id),
                        'message' => 'Dokumen MR baru dibuat oleh ' . ($mr->creator->name ?? 'Staff') . '. Silakan atur daftar approval.',
                        'time' => $mr->created_at->diffForHumans(),
                        'sort_at' => $mr->created_at,
                        'url' => route('material-requests.show', $mr),
                    ];
                } else {
                    $notifications[] = [
                        'id' => 'admin_new_mr_' . $mr->id,
                        'module' => 'MR',
                        'module_badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'icon_bg' => 'bg-indigo-50 text-indigo-600',
                        'icon_type' => 'document',
                        'status_label' => 'Dokumen Baru',
                        'status_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'title' => 'MR Baru: ' . ($mr->no_mr ?? 'MR-' . $mr->id),
                        'message' => 'Dokumen MR baru diajukan oleh ' . ($mr->creator->name ?? 'Staff') . '. Alur persetujuan sedang berjalan.',
                        'time' => $mr->created_at->diffForHumans(),
                        'sort_at' => $mr->created_at,
                        'url' => route('material-requests.show', $mr),
                    ];
                }
            }

            $recentRlps = Rlp::with(['creator', 'approvals'])->latest('created_at')->take(5)->get();
            foreach ($recentRlps as $rlp) {
                $hasApprover = $rlp->approvals->contains(fn($q) => $q->step > 1);
                $isDone = ($rlp->status === 'Done' || $rlp->is_approved);
                $isRejected = ($rlp->is_rejected || str_starts_with($rlp->status ?? '', 'Rejected'));
                if ($isDone || $isRejected) continue;

                if (! $hasApprover) {
                    $notifications[] = [
                        'id' => 'admin_unassigned_rlp_' . $rlp->id,
                        'module' => 'RRP',
                        'module_badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'icon_bg' => 'bg-purple-50 text-purple-600',
                        'icon_type' => 'document',
                        'status_label' => 'Perlu Approver',
                        'status_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'title' => 'Atur Approver: ' . ($rlp->no_rlp ?? 'RRP-' . $rlp->id),
                        'message' => 'Dokumen RRP baru dibuat oleh ' . ($rlp->creator->name ?? 'Staff') . '. Silakan atur daftar approval.',
                        'time' => $rlp->created_at->diffForHumans(),
                        'sort_at' => $rlp->created_at,
                        'url' => route('rlps.show', $rlp),
                    ];
                } else {
                    $notifications[] = [
                        'id' => 'admin_new_rlp_' . $rlp->id,
                        'module' => 'RRP',
                        'module_badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'icon_bg' => 'bg-purple-50 text-purple-600',
                        'icon_type' => 'document',
                        'status_label' => 'Dokumen Baru',
                        'status_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'title' => 'RRP Baru: ' . ($rlp->no_rlp ?? 'RRP-' . $rlp->id),
                        'message' => 'Dokumen RRP baru diajukan oleh ' . ($rlp->creator->name ?? 'Staff') . '. Alur persetujuan sedang berjalan.',
                        'time' => $rlp->created_at->diffForHumans(),
                        'sort_at' => $rlp->created_at,
                        'url' => route('rlps.show', $rlp),
                    ];
                }
            }

            $recentPos = PurchaseOrder::with(['creator', 'approvals'])->latest('created_at')->take(5)->get();
            foreach ($recentPos as $po) {
                $hasApprover = $po->approvals->contains(fn($q) => $q->step > 1);
                $isDone = ($po->status === 'Done' || $po->is_approved);
                $isRejected = ($po->is_rejected || str_starts_with($po->status ?? '', 'Rejected'));
                if ($isDone || $isRejected) continue;

                if (! $hasApprover) {
                    $notifications[] = [
                        'id' => 'admin_unassigned_po_' . $po->id,
                        'module' => 'PO',
                        'module_badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'icon_bg' => 'bg-purple-50 text-purple-600',
                        'icon_type' => 'document',
                        'status_label' => 'Perlu Approver',
                        'status_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'title' => 'Atur Approver: ' . ($po->po_no ?? 'PO-' . $po->id),
                        'message' => 'Dokumen PO baru dibuat oleh ' . ($po->creator->name ?? 'Staff') . '. Silakan atur daftar approval.',
                        'time' => $po->created_at->diffForHumans(),
                        'sort_at' => $po->created_at,
                        'url' => route('purchase-orders.show', $po),
                    ];
                } else {
                    $notifications[] = [
                        'id' => 'admin_new_po_' . $po->id,
                        'module' => 'PO',
                        'module_badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'icon_bg' => 'bg-emerald-50 text-emerald-600',
                        'icon_type' => 'document',
                        'status_label' => 'Dokumen Baru',
                        'status_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'title' => 'PO Baru: ' . ($po->po_no ?? 'PO-' . $po->id),
                        'message' => 'Dokumen PO baru dibuat oleh ' . ($po->creator->name ?? 'Staff') . '. Alur persetujuan sedang berjalan.',
                        'time' => $po->created_at->diffForHumans(),
                        'sort_at' => $po->created_at,
                        'url' => route('purchase-orders.show', $po),
                    ];
                }
            }

            $recentPrs = PurchaseRequest::with(['creator', 'approvals'])->latest('created_at')->take(5)->get();
            foreach ($recentPrs as $pr) {
                $hasApprover = $pr->approvals->contains(fn($q) => $q->step > 1);
                $isDone = ($pr->status === 'Done' || $pr->is_approved);
                $isRejected = ($pr->is_rejected || str_starts_with($pr->status ?? '', 'Rejected'));
                if ($isDone || $isRejected) continue;

                if (! $hasApprover) {
                    $notifications[] = [
                        'id' => 'admin_unassigned_pr_' . $pr->id,
                        'module' => 'PR',
                        'module_badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'icon_bg' => 'bg-purple-50 text-purple-600',
                        'icon_type' => 'document',
                        'status_label' => 'Perlu Approver',
                        'status_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'title' => 'Atur Approver: ' . ($pr->no_request ?? 'PR-' . $pr->id),
                        'message' => 'Dokumen PR baru dibuat oleh ' . ($pr->creator->name ?? 'Staff') . '. Silakan atur daftar approval.',
                        'time' => $pr->created_at->diffForHumans(),
                        'sort_at' => $pr->created_at,
                        'url' => route('purchase-requests.show', $pr),
                    ];
                } else {
                    $notifications[] = [
                        'id' => 'admin_new_pr_' . $pr->id,
                        'module' => 'PR',
                        'module_badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'icon_bg' => 'bg-amber-50 text-amber-600',
                        'icon_type' => 'document',
                        'status_label' => 'Dokumen Baru',
                        'status_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'title' => 'PR Baru: ' . ($pr->no_request ?? 'PR-' . $pr->id),
                        'message' => 'Dokumen PR baru dibuat oleh ' . ($pr->creator->name ?? 'Staff') . '. Alur persetujuan sedang berjalan.',
                        'time' => $pr->created_at->diffForHumans(),
                        'sort_at' => $pr->created_at,
                        'url' => route('purchase-requests.show', $pr),
                    ];
                }
            }

            // 2. Overdue Approvals across all documents
            $allUnsigned = DocumentApproval::whereNull('signed_at')->with('user')->get();
            foreach ($allUnsigned as $appr) {
                if ($appr->isOverdue()) {
                    $docName = $appr->document_module . ' #' . $appr->document_id;
                    $notifications[] = [
                        'id' => 'admin_overdue_appr_' . $appr->id,
                        'module' => $appr->document_module,
                        'module_badge_class' => $appr->document_badge_class,
                        'icon_bg' => 'bg-red-50 text-red-600',
                        'icon_type' => 'warning',
                        'status_label' => 'Overdue (>2 Hari)',
                        'status_class' => 'bg-red-50 text-red-700 border-red-200',
                        'title' => 'Approval Terlewat: ' . $docName,
                        'message' => "Tahap '{$appr->label}' oleh " . ($appr->user->name ?? 'Approver') . " telah melewati 2 hari dan otomatis diteruskan ke tahap berikutnya.",
                        'time' => $appr->created_at->diffForHumans(),
                        'sort_at' => $appr->created_at,
                        'url' => $appr->document_url,
                    ];
                }
            }

            // 3. Dokumen yang Ditolak (Rejected Approvals)
            $rejectedApprovals = DocumentApproval::whereNotNull('rejected_at')
                ->with('user')
                ->latest('rejected_at')
                ->take(10)
                ->get();

            foreach ($rejectedApprovals as $appr) {
                $docNumber = $appr->document_number;
                $notifications[] = [
                    'id' => 'admin_rejected_appr_' . $appr->id,
                    'module' => $appr->document_module,
                    'module_badge_class' => $appr->document_badge_class,
                    'icon_bg' => 'bg-red-50 text-red-600',
                    'icon_type' => 'rejected',
                    'status_label' => 'Ditolak',
                    'status_class' => 'bg-red-50 text-red-700 border-red-200',
                    'title' => 'Dokumen Ditolak: ' . $docNumber,
                    'message' => "Tahap '{$appr->label}' ditolak oleh " . ($appr->user->name ?? 'Approver') . ($appr->rejection_reason ? ". Alasan: {$appr->rejection_reason}" : '.'),
                    'time' => $appr->rejected_at->diffForHumans(),
                    'sort_at' => $appr->rejected_at,
                    'url' => $appr->document_url,
                ];
            }
        } elseif ($user->isApprover()) {
            // ==========================================
            // APPROVER NOTIFICATIONS
            // Tugas tanda tangan yang ditugaskan ke user ini dan sudah terbuka (unlocked)
            // ==========================================
            $myApprovals = DocumentApproval::where('user_id', $user->id)
                ->whereNull('signed_at')
                ->whereNull('rejected_at')
                ->get();

            foreach ($myApprovals as $appr) {
                if (! $appr->isUnlocked()) {
                    continue; // Belum gilirannya atau dokumen telah ditolak
                }

                $isOverdue = $appr->isOverdue();
                $docName = $appr->document_module . ' #' . $appr->document_id;

                $notifications[] = [
                    'id' => 'approver_task_' . $appr->id,
                    'module' => $appr->document_module,
                    'module_badge_class' => $appr->document_badge_class,
                    'icon_bg' => $isOverdue ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600',
                    'icon_type' => $isOverdue ? 'warning' : 'clock',
                    'status_label' => $isOverdue ? 'Batas Waktu Lewat' : 'Perlu Tanda Tangan',
                    'status_class' => $isOverdue ? 'bg-red-50 text-red-700 border-red-200' : 'bg-yellow-50 text-yellow-800 border-yellow-200',
                    'title' => ($isOverdue ? 'Segera TTD: ' : 'Menunggu TTD: ') . $docName,
                    'message' => "Anda ditugaskan sebagai '{$appr->label}' pada {$docName}. Silakan buka dokumen dan tanda tangani.",
                    'time' => $appr->created_at->diffForHumans(),
                    'sort_at' => $appr->created_at,
                    'url' => $appr->document_url,
                ];
            }

            // Dokumen yang ditolak (yang melibatkan approver ini atau dibuat oleh approver ini)
            $myRejectedApprovals = DocumentApproval::whereNotNull('rejected_at')
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhereExists(function ($sub) use ($user) {
                            $sub->select(DB::raw(1))
                                ->from('document_approvals as da2')
                                ->whereColumn('da2.document_type', 'document_approvals.document_type')
                                ->whereColumn('da2.document_id', 'document_approvals.document_id')
                                ->where('da2.user_id', $user->id);
                        });
                })
                ->with('user')
                ->latest('rejected_at')
                ->take(6)
                ->get();

            foreach ($myRejectedApprovals as $appr) {
                $isMe = $appr->user_id === $user->id;
                $notifications[] = [
                    'id' => 'approver_rejected_appr_' . $appr->id,
                    'module' => $appr->document_module,
                    'module_badge_class' => $appr->document_badge_class,
                    'icon_bg' => 'bg-red-50 text-red-600',
                    'icon_type' => 'rejected',
                    'status_label' => 'Ditolak',
                    'status_class' => 'bg-red-50 text-red-700 border-red-200',
                    'title' => 'Dokumen Ditolak: ' . $appr->document_number,
                    'message' => $isMe
                        ? "Anda telah menolak tahap '{$appr->label}'" . ($appr->rejection_reason ? ". Alasan: {$appr->rejection_reason}" : '.')
                        : "Tahap '{$appr->label}' ditolak oleh " . ($appr->user->name ?? 'Approver') . ($appr->rejection_reason ? ". Alasan: {$appr->rejection_reason}" : '.'),
                    'time' => $appr->rejected_at->diffForHumans(),
                    'sort_at' => $appr->rejected_at,
                    'url' => $appr->document_url,
                ];
            }
        } else {
            // ==========================================
            // INPUTER NOTIFICATIONS
            // Status dokumen milik user ini yang ditandatangani, ditolak, atau diselesaikan
            // ==========================================

            // 1. Dokumen milik inputer yang ditolak (selalu diprioritaskan)
            $myRejectedApprovals = DocumentApproval::whereNotNull('rejected_at')
                ->whereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))
                        ->from('document_approvals as da_creator')
                        ->whereColumn('da_creator.document_type', 'document_approvals.document_type')
                        ->whereColumn('da_creator.document_id', 'document_approvals.document_id')
                        ->where('da_creator.step', 1)
                        ->where('da_creator.user_id', $user->id);
                })
                ->with('user')
                ->latest('rejected_at')
                ->take(6)
                ->get();

            foreach ($myRejectedApprovals as $appr) {
                $notifications[] = [
                    'id' => 'inputer_' . $appr->document_type . '_rejected_' . $appr->document_id,
                    'module' => $appr->document_module,
                    'module_badge_class' => $appr->document_badge_class,
                    'icon_bg' => 'bg-red-50 text-red-600',
                    'icon_type' => 'rejected',
                    'status_label' => 'Ditolak',
                    'status_class' => 'bg-red-50 text-red-700 border-red-200',
                    'title' => 'Dokumen Ditolak: ' . $appr->document_number,
                    'message' => "Tahap '{$appr->label}' ditolak oleh " . ($appr->user->name ?? 'Approver') . ($appr->rejection_reason ? ". Alasan: {$appr->rejection_reason}" : '.'),
                    'time' => $appr->rejected_at->diffForHumans(),
                    'sort_at' => $appr->rejected_at,
                    'url' => $appr->document_url,
                ];
            }

            // 2. Material Requests
            $myMrs = MaterialRequest::where('created_by', $user->id)->with('approvals.user')->latest('updated_at')->take(4)->get();
            foreach ($myMrs as $mr) {
                $rejected = $mr->approvals->whereNotNull('rejected_at')->sortByDesc('rejected_at')->first();
                if ($rejected) {
                    $notifications[] = [
                        'id' => 'inputer_mr_rejected_' . $mr->id,
                        'module' => 'MR',
                        'module_badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'icon_bg' => 'bg-red-50 text-red-600',
                        'icon_type' => 'rejected',
                        'status_label' => 'Ditolak',
                        'status_class' => 'bg-red-50 text-red-700 border-red-200',
                        'title' => 'Dokumen Ditolak: ' . ($mr->no_mr ?? 'MR-' . $mr->id),
                        'message' => "Tahap '{$rejected->label}' ditolak oleh " . ($rejected->user->name ?? 'Approver') . ($rejected->rejection_reason ? ". Alasan: {$rejected->rejection_reason}" : '.'),
                        'time' => $rejected->rejected_at ? $rejected->rejected_at->diffForHumans() : $mr->updated_at->diffForHumans(),
                        'sort_at' => $rejected->rejected_at ?? $mr->updated_at,
                        'url' => route('material-requests.show', $mr),
                    ];
                    continue;
                }

                $signedCount = $mr->approvals->whereNotNull('signed_at')->count();
                $totalCount = $mr->approvals->count();
                $hasConfiguredApprovals = $mr->approvals->contains(fn ($a) => $a->step > 1);
                if (! $hasConfiguredApprovals) {
                    $notifications[] = [
                        'id' => 'inputer_mr_wait_' . $mr->id,
                        'module' => 'MR',
                        'module_badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'icon_bg' => 'bg-amber-50 text-amber-600',
                        'icon_type' => 'clock',
                        'status_label' => 'Menunggu Admin',
                        'status_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'title' => 'Menunggu Super Admin: ' . ($mr->no_mr ?? 'MR-' . $mr->id),
                        'message' => 'MR berhasil diajukan dan sedang menunggu konfigurasi tanda tangan oleh Super Admin.',
                        'time' => $mr->created_at->diffForHumans(),
                        'sort_at' => $mr->created_at,
                        'url' => route('material-requests.show', $mr),
                    ];
                } elseif ($signedCount === $totalCount && $totalCount > 0) {
                    $notifications[] = [
                        'id' => 'inputer_mr_done_' . $mr->id,
                        'module' => 'MR',
                        'module_badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'icon_bg' => 'bg-emerald-50 text-emerald-600',
                        'icon_type' => 'document',
                        'status_label' => 'Disetujui Penuh',
                        'status_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'title' => 'Selesai: ' . ($mr->no_mr ?? 'MR-' . $mr->id),
                        'message' => "Semua approval ({$totalCount}/{$totalCount}) telah ditandatangani. Dokumen selesai diproses.",
                        'time' => $mr->updated_at->diffForHumans(),
                        'sort_at' => $mr->updated_at,
                        'url' => route('material-requests.show', $mr),
                    ];
                } else {
                    $lastSigned = $mr->approvals->whereNotNull('signed_at')->sortByDesc('signed_at')->first();
                    $notifications[] = [
                        'id' => 'inputer_mr_progress_' . $mr->id,
                        'module' => 'MR',
                        'module_badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'icon_bg' => 'bg-indigo-50 text-indigo-600',
                        'icon_type' => 'document',
                        'status_label' => "Proses ({$signedCount}/{$totalCount})",
                        'status_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'title' => 'Progress Approval: ' . ($mr->no_mr ?? 'MR-' . $mr->id),
                        'message' => $lastSigned ? "Tahap '{$lastSigned->label}' telah ditandatangani oleh " . ($lastSigned->user->name ?? 'Approver') . "." : "Sedang dalam proses tanda tangan.",
                        'time' => $mr->updated_at->diffForHumans(),
                        'sort_at' => $mr->updated_at,
                        'url' => route('material-requests.show', $mr),
                    ];
                }
            }

            // 3. RRP / RLP
            $myRlps = Rlp::where('created_by', $user->id)->with('approvals.user')->latest('updated_at')->take(4)->get();
            foreach ($myRlps as $rlp) {
                $rejected = $rlp->approvals->whereNotNull('rejected_at')->sortByDesc('rejected_at')->first();
                if ($rejected) {
                    $notifications[] = [
                        'id' => 'inputer_rlp_rejected_' . $rlp->id,
                        'module' => 'RRP',
                        'module_badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'icon_bg' => 'bg-red-50 text-red-600',
                        'icon_type' => 'rejected',
                        'status_label' => 'Ditolak',
                        'status_class' => 'bg-red-50 text-red-700 border-red-200',
                        'title' => 'Dokumen Ditolak: ' . ($rlp->no_rlp ?? 'RRP-' . $rlp->id),
                        'message' => "Tahap '{$rejected->label}' ditolak oleh " . ($rejected->user->name ?? 'Approver') . ($rejected->rejection_reason ? ". Alasan: {$rejected->rejection_reason}" : '.'),
                        'time' => $rejected->rejected_at ? $rejected->rejected_at->diffForHumans() : $rlp->updated_at->diffForHumans(),
                        'sort_at' => $rejected->rejected_at ?? $rlp->updated_at,
                        'url' => route('rlps.show', $rlp),
                    ];
                    continue;
                }

                $signedCount = $rlp->approvals->whereNotNull('signed_at')->count();
                $totalCount = $rlp->approvals->count();
                $hasConfiguredApprovals = $rlp->approvals->contains(fn ($a) => $a->step > 1);
                if (! $hasConfiguredApprovals) {
                    $notifications[] = [
                        'id' => 'inputer_rlp_wait_' . $rlp->id,
                        'module' => 'RRP',
                        'module_badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'icon_bg' => 'bg-amber-50 text-amber-600',
                        'icon_type' => 'clock',
                        'status_label' => 'Menunggu Admin',
                        'status_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'title' => 'Menunggu Super Admin: ' . ($rlp->no_rlp ?? 'RRP-' . $rlp->id),
                        'message' => 'RRP berhasil diajukan dan sedang menunggu konfigurasi tanda tangan oleh Super Admin.',
                        'time' => $rlp->created_at->diffForHumans(),
                        'sort_at' => $rlp->created_at,
                        'url' => route('rlps.show', $rlp),
                    ];
                } elseif ($signedCount === $totalCount && $totalCount > 0) {
                    $notifications[] = [
                        'id' => 'inputer_rlp_done_' . $rlp->id,
                        'module' => 'RRP',
                        'module_badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'icon_bg' => 'bg-emerald-50 text-emerald-600',
                        'icon_type' => 'document',
                        'status_label' => 'Disetujui Penuh',
                        'status_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'title' => 'Selesai: ' . ($rlp->no_rlp ?? 'RRP-' . $rlp->id),
                        'message' => "Semua approval ({$totalCount}/{$totalCount}) telah ditandatangani. Dokumen selesai diproses.",
                        'time' => $rlp->updated_at->diffForHumans(),
                        'sort_at' => $rlp->updated_at,
                        'url' => route('rlps.show', $rlp),
                    ];
                } else {
                    $lastSigned = $rlp->approvals->whereNotNull('signed_at')->sortByDesc('signed_at')->first();
                    $notifications[] = [
                        'id' => 'inputer_rlp_progress_' . $rlp->id,
                        'module' => 'RRP',
                        'module_badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'icon_bg' => 'bg-purple-50 text-purple-600',
                        'icon_type' => 'document',
                        'status_label' => "Proses ({$signedCount}/{$totalCount})",
                        'status_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'title' => 'Progress Approval: ' . ($rlp->no_rlp ?? 'RRP-' . $rlp->id),
                        'message' => $lastSigned ? "Tahap '{$lastSigned->label}' telah ditandatangani oleh " . ($lastSigned->user->name ?? 'Approver') . "." : "Sedang dalam proses tanda tangan.",
                        'time' => $rlp->updated_at->diffForHumans(),
                        'sort_at' => $rlp->updated_at,
                        'url' => route('rlps.show', $rlp),
                    ];
                }
            }

            // 4. Purchase Order
            $myPos = PurchaseOrder::where('created_by', $user->id)->with('approvals.user')->latest('updated_at')->take(4)->get();
            foreach ($myPos as $po) {
                $rejected = $po->approvals->whereNotNull('rejected_at')->sortByDesc('rejected_at')->first();
                if ($rejected) {
                    $notifications[] = [
                        'id' => 'inputer_po_rejected_' . $po->id,
                        'module' => 'PO',
                        'module_badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'icon_bg' => 'bg-red-50 text-red-600',
                        'icon_type' => 'rejected',
                        'status_label' => 'Ditolak',
                        'status_class' => 'bg-red-50 text-red-700 border-red-200',
                        'title' => 'Dokumen Ditolak: ' . ($po->po_no ?? 'PO-' . $po->id),
                        'message' => "Tahap '{$rejected->label}' ditolak oleh " . ($rejected->user->name ?? 'Approver') . ($rejected->rejection_reason ? ". Alasan: {$rejected->rejection_reason}" : '.'),
                        'time' => $rejected->rejected_at ? $rejected->rejected_at->diffForHumans() : $po->updated_at->diffForHumans(),
                        'sort_at' => $rejected->rejected_at ?? $po->updated_at,
                        'url' => route('purchase-orders.show', $po),
                    ];
                    continue;
                }

                $signedCount = $po->approvals->whereNotNull('signed_at')->count();
                $totalCount = $po->approvals->count();
                $hasConfiguredApprovals = $po->approvals->contains(fn ($a) => $a->step > 1);
                if (! $hasConfiguredApprovals) {
                    $notifications[] = [
                        'id' => 'inputer_po_wait_' . $po->id,
                        'module' => 'PO',
                        'module_badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'icon_bg' => 'bg-amber-50 text-amber-600',
                        'icon_type' => 'clock',
                        'status_label' => 'Menunggu Admin',
                        'status_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'title' => 'Menunggu Super Admin: ' . ($po->po_no ?? 'PO-' . $po->id),
                        'message' => 'PO berhasil dibuat dan sedang menunggu konfigurasi tanda tangan oleh Super Admin.',
                        'time' => $po->created_at->diffForHumans(),
                        'sort_at' => $po->created_at,
                        'url' => route('purchase-orders.show', $po),
                    ];
                } elseif ($signedCount === $totalCount && $totalCount > 0) {
                    $notifications[] = [
                        'id' => 'inputer_po_done_' . $po->id,
                        'module' => 'PO',
                        'module_badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'icon_bg' => 'bg-emerald-50 text-emerald-600',
                        'icon_type' => 'document',
                        'status_label' => 'Disetujui Penuh',
                        'status_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'title' => 'Selesai: ' . ($po->po_no ?? 'PO-' . $po->id),
                        'message' => "Semua approval ({$totalCount}/{$totalCount}) telah ditandatangani. PO siap diproses.",
                        'time' => $po->updated_at->diffForHumans(),
                        'sort_at' => $po->updated_at,
                        'url' => route('purchase-orders.show', $po),
                    ];
                } else {
                    $lastSigned = $po->approvals->whereNotNull('signed_at')->sortByDesc('signed_at')->first();
                    $notifications[] = [
                        'id' => 'inputer_po_progress_' . $po->id,
                        'module' => 'PO',
                        'module_badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'icon_bg' => 'bg-emerald-50 text-emerald-600',
                        'icon_type' => 'document',
                        'status_label' => "Proses ({$signedCount}/{$totalCount})",
                        'status_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'title' => 'Progress Approval: ' . ($po->po_no ?? 'PO-' . $po->id),
                        'message' => $lastSigned ? "Tahap '{$lastSigned->label}' telah ditandatangani oleh " . ($lastSigned->user->name ?? 'Approver') . "." : "Sedang dalam proses tanda tangan.",
                        'time' => $po->updated_at->diffForHumans(),
                        'sort_at' => $po->updated_at,
                        'url' => route('purchase-orders.show', $po),
                    ];
                }
            }

            // 5. Purchase Request
            $myPrs = PurchaseRequest::where('created_by', $user->id)->with('approvals.user')->latest('updated_at')->take(4)->get();
            foreach ($myPrs as $pr) {
                $rejected = $pr->approvals->whereNotNull('rejected_at')->sortByDesc('rejected_at')->first();
                if ($rejected) {
                    $notifications[] = [
                        'id' => 'inputer_pr_rejected_' . $pr->id,
                        'module' => 'PR',
                        'module_badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'icon_bg' => 'bg-red-50 text-red-600',
                        'icon_type' => 'rejected',
                        'status_label' => 'Ditolak',
                        'status_class' => 'bg-red-50 text-red-700 border-red-200',
                        'title' => 'Dokumen Ditolak: ' . ($pr->no_request ?? 'PR-' . $pr->id),
                        'message' => "Tahap '{$rejected->label}' ditolak oleh " . ($rejected->user->name ?? 'Approver') . ($rejected->rejection_reason ? ". Alasan: {$rejected->rejection_reason}" : '.'),
                        'time' => $rejected->rejected_at ? $rejected->rejected_at->diffForHumans() : $pr->updated_at->diffForHumans(),
                        'sort_at' => $rejected->rejected_at ?? $pr->updated_at,
                        'url' => route('purchase-requests.show', $pr),
                    ];
                    continue;
                }

                $signedCount = $pr->approvals->whereNotNull('signed_at')->count();
                $totalCount = $pr->approvals->count();
                $hasConfiguredApprovals = $pr->approvals->contains(fn ($a) => $a->step > 1);
                if (! $hasConfiguredApprovals) {
                    $notifications[] = [
                        'id' => 'inputer_pr_wait_' . $pr->id,
                        'module' => 'PR',
                        'module_badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'icon_bg' => 'bg-amber-50 text-amber-600',
                        'icon_type' => 'clock',
                        'status_label' => 'Menunggu Admin',
                        'status_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'title' => 'Menunggu Super Admin: ' . ($pr->no_request ?? 'PR-' . $pr->id),
                        'message' => 'PR berhasil dibuat dan sedang menunggu konfigurasi tanda tangan oleh Super Admin.',
                        'time' => $pr->created_at->diffForHumans(),
                        'sort_at' => $pr->created_at,
                        'url' => route('purchase-requests.show', $pr),
                    ];
                } elseif ($signedCount === $totalCount && $totalCount > 0) {
                    $notifications[] = [
                        'id' => 'inputer_pr_done_' . $pr->id,
                        'module' => 'PR',
                        'module_badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'icon_bg' => 'bg-emerald-50 text-emerald-600',
                        'icon_type' => 'document',
                        'status_label' => 'Disetujui Penuh',
                        'status_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'title' => 'Selesai: ' . ($pr->no_request ?? 'PR-' . $pr->id),
                        'message' => "Semua approval ({$totalCount}/{$totalCount}) telah ditandatangani. PR siap diproses.",
                        'time' => $pr->updated_at->diffForHumans(),
                        'sort_at' => $pr->updated_at,
                        'url' => route('purchase-requests.show', $pr),
                    ];
                } else {
                    $lastSigned = $pr->approvals->whereNotNull('signed_at')->sortByDesc('signed_at')->first();
                    $notifications[] = [
                        'id' => 'inputer_pr_progress_' . $pr->id,
                        'module' => 'PR',
                        'module_badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'icon_bg' => 'bg-amber-50 text-amber-600',
                        'icon_type' => 'document',
                        'status_label' => "Proses ({$signedCount}/{$totalCount})",
                        'status_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'title' => 'Progress Approval: ' . ($pr->no_request ?? 'PR-' . $pr->id),
                        'message' => $lastSigned ? "Tahap '{$lastSigned->label}' telah ditandatangani oleh " . ($lastSigned->user->name ?? 'Approver') . "." : "Sedang dalam proses tanda tangan.",
                        'time' => $pr->updated_at->diffForHumans(),
                        'sort_at' => $pr->updated_at,
                        'url' => route('purchase-requests.show', $pr),
                    ];
                }
            }
        }

        // Filter duplikat notifikasi berdasarkan ID unik
        $uniqueNotifications = [];
        foreach ($notifications as $item) {
            if (! isset($uniqueNotifications[$item['id']])) {
                $uniqueNotifications[$item['id']] = $item;
            }
        }
        $notifications = array_values($uniqueNotifications);

        // Urutkan notifikasi terbaru tampil paling atas
        usort($notifications, fn ($a, $b) => $b['sort_at'] <=> $a['sort_at']);

        $notifications = array_map(function ($item) {
            unset($item['sort_at']);
            return $item;
        }, array_slice($notifications, 0, 15));

        $maxMr = MaterialRequest::max('id') ?? 0;
        $maxRlp = Rlp::max('id') ?? 0;
        $maxPr = PurchaseRequest::max('id') ?? 0;
        $maxPo = PurchaseOrder::max('id') ?? 0;
        $maxAppr = DocumentApproval::max('id') ?? 0;
        $maxApprUpdate = DocumentApproval::max('updated_at') ?? '';

        $systemVersion = "mr:{$maxMr}|rlp:{$maxRlp}|pr:{$maxPr}|po:{$maxPo}|appr:{$maxAppr}|up:{$maxApprUpdate}";

        // Cari dokumen yang paling baru dibuat di sistem
        $latestMr = MaterialRequest::with('creator')->latest('id')->first();
        $latestRlp = Rlp::with('creator')->latest('id')->first();
        $latestPr = PurchaseRequest::with('creator')->latest('id')->first();
        $latestPo = PurchaseOrder::with('creator')->latest('id')->first();

        $candidates = collect();
        if ($latestMr && $latestMr->created_at) {
            $candidates->push([
                'type' => 'MR',
                'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'id' => $latestMr->id,
                'number' => $latestMr->no_mr ?? ('MR-' . $latestMr->id),
                'creator_id' => $latestMr->created_by,
                'creator_name' => $latestMr->creator->name ?? 'Staff',
                'created_at' => $latestMr->created_at,
                'time_human' => $latestMr->created_at->diffForHumans(),
                'url' => route('material-requests.show', $latestMr),
                'module_title' => 'Material Request (MR)',
            ]);
        }
        if ($latestRlp && $latestRlp->created_at) {
            $candidates->push([
                'type' => 'RRP',
                'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                'id' => $latestRlp->id,
                'number' => $latestRlp->no_rlp ?? ('RRP-' . $latestRlp->id),
                'creator_id' => $latestRlp->created_by,
                'creator_name' => $latestRlp->creator->name ?? 'Staff',
                'created_at' => $latestRlp->created_at,
                'time_human' => $latestRlp->created_at->diffForHumans(),
                'url' => route('rlps.show', $latestRlp),
                'module_title' => 'Local Purchase (RRP)',
            ]);
        }
        if ($latestPr && $latestPr->created_at) {
            $candidates->push([
                'type' => 'PR',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'id' => $latestPr->id,
                'number' => $latestPr->no_request ?? ('PR-' . $latestPr->id),
                'creator_id' => $latestPr->created_by,
                'creator_name' => $latestPr->creator->name ?? 'Staff',
                'created_at' => $latestPr->created_at,
                'time_human' => $latestPr->created_at->diffForHumans(),
                'url' => route('purchase-requests.show', $latestPr),
                'module_title' => 'Purchase Request (PR)',
            ]);
        }
        if ($latestPo && $latestPo->created_at) {
            $candidates->push([
                'type' => 'PO',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'id' => $latestPo->id,
                'number' => $latestPo->po_no ?? ('PO-' . $latestPo->id),
                'creator_id' => $latestPo->created_by,
                'creator_name' => $latestPo->creator->name ?? 'Staff',
                'created_at' => $latestPo->created_at,
                'time_human' => $latestPo->created_at->diffForHumans(),
                'url' => route('purchase-orders.show', $latestPo),
                'module_title' => 'Purchase Order (PO)',
            ]);
        }

        $latestDocument = $candidates->sortByDesc('created_at')->first();
        if ($latestDocument) {
            unset($latestDocument['created_at']);
        }

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => count($notifications),
            'system_version' => $systemVersion,
            'latest_document' => $latestDocument,
        ]);
    }
}
