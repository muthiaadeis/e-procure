<?php

namespace App\Http\Controllers;

class PurchaseOrderController extends Controller
{
    // Modul PO belum dibangun — tampilkan halaman "coming soon" dulu
    // biar menu & layout-nya sudah konsisten dengan sisa aplikasi.
    public function index()
    {
        return view('purchase_orders.index');
    }
}
