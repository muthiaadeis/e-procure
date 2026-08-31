<?php

namespace App\Http\Controllers;

class PurchaseRequestController extends Controller
{
    // Modul PR belum dibangun — tampilkan halaman "coming soon" dulu
    // biar menu & layout-nya sudah konsisten dengan sisa aplikasi.
    public function index()
    {
        return view('purchase_requests.index');
    }
}
