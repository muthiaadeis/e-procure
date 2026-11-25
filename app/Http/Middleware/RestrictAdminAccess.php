<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictAdminAccess
{
    /**
     * Super Admin memiliki tampilan yang sama dengan sistem biasa
     * (Dashboard, Pengadaan: MR, RRP, PO, PR, Data Master: Vendors, Job Code, dan Administrasi).
     * Super Admin dapat melihat dan mengontrol seluruh dokumen,
     * tetapi tidak dapat membuat/menginput dokumen baru (dijaga oleh role isInput).
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
