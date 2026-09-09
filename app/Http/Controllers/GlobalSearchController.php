<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Rlp;
use App\Models\Vendor;
use Illuminate\Http\Request;

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
                'url' => route('material-requests.index', ['search' => $mr->no_mr, 'auto_open' => $mr->id]),
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
                'url' => route('rlps.index'),
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
                'subtitle' => ($vendor->vendor_code ? $vendor->vendor_code . ' • ' : '') . ($vendor->email ?? ($vendor->phone ?? 'Vendor Aktif')),
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
        $notifications = [];

        // 1. Overdue MRs
        $overdueMrs = MaterialRequest::latest('created_at')->get()->filter(fn($m) => $m->is_overdue && $m->status !== 'Done')->take(3);
        foreach ($overdueMrs as $m) {
            $notifications[] = [
                'id' => 'mr_overdue_' . $m->id,
                'module' => 'MR',
                'module_badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'icon_bg' => 'bg-red-50 text-red-600',
                'icon_type' => 'warning',
                'status_label' => 'Terlambat',
                'status_label' => 'Overdue',
                'status_class' => 'bg-red-50 text-red-700 border-red-200',
                'title' => 'Permintaan Terlambat: ' . ($m->no_mr ?? 'MR-' . $m->id),
                'message' => 'Material request untuk ' . ($m->charge_to ?? 'proyek') . ' telah melebihi batas waktu approval.',
                'title' => 'Overdue Request: ' . ($m->no_mr ?? 'MR-' . $m->id),
                'message' => 'Material request for ' . ($m->charge_to ?? 'project') . ' has exceeded the approval deadline.',
                'time' => $m->created_at->diffForHumans(),
                'url' => route('material-requests.index', ['search' => $m->no_mr, 'filter' => 'overdue', 'auto_open' => $m->id]),
            ];
        }

        // 2. Pending Approval MRs
        $pendingMrs = MaterialRequest::whereNull('approved_a_at')->latest('created_at')->take(2)->get();
        foreach ($pendingMrs as $m) {
            $notifications[] = [
                'id' => 'mr_pending_' . $m->id,
                'module' => 'MR',
                'module_badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'icon_type' => 'clock',
                'status_label' => 'Approval A',
                'status_label' => 'Pending Approval',
                'status_class' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
                'title' => 'Menunggu Approval: ' . ($m->no_mr ?? 'MR-' . $m->id),
                'message' => 'Membutuhkan persetujuan Approver A (' . ($m->charge_to ?? 'Umum') . ').',
                'title' => 'Awaiting Approval: ' . ($m->no_mr ?? 'MR-' . $m->id),
                'message' => 'Requires Approver A authorization (' . ($m->charge_to ?? 'General') . ').',
                'time' => $m->created_at->diffForHumans(),
                'url' => route('material-requests.index', ['search' => $m->no_mr, 'filter' => 'pending_approval', 'auto_open' => $m->id]),
            ];
        }

        // 3. Pending RLPs
        $pendingRlps = Rlp::whereNull('approved_at')->latest('created_at')->take(2)->get();
        foreach ($pendingRlps as $r) {
            $notifications[] = [
                'id' => 'rlp_pending_' . $r->id,
                'module' => 'RLP',
                'module_badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                'icon_bg' => 'bg-purple-50 text-purple-600',
                'icon_type' => 'document',
                'status_label' => 'Review',
                'status_label' => 'Under Review',
                'status_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                'title' => 'Local Purchase: ' . ($r->no_rlp ?? 'RLP-' . $r->id),
                'message' => 'Membutuhkan review dan verifikasi dokumen pengadaan lokal.',
                'message' => 'Requires review and verification of local purchase document.',
                'time' => $r->created_at->diffForHumans(),
                'url' => route('rlps.index'),
            ];
        }

        // 4. PR & PO
        $prs = PurchaseRequest::latest('created_at')->take(1)->get();
        foreach ($prs as $pr) {
            $notifications[] = [
                'id' => 'pr_' . $pr->id,
                'module' => 'PR',
                'module_badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'icon_type' => 'cart',
                'status_label' => 'Dokumen Baru',
                'status_label' => 'New Document',
                'status_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'title' => 'Purchase Request: ' . ($pr->no_request ?? 'PR-' . $pr->id),
                'message' => $pr->title ?? 'Dokumen PR telah diterbitkan.',
                'message' => $pr->title ?? 'Purchase request document has been issued.',
                'time' => $pr->created_at->diffForHumans(),
                'url' => route('purchase-requests.show', $pr),
            ];
        }

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => count($notifications),
        ]);
    }
}
