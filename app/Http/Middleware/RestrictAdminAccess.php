<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictAdminAccess
{
    /**
     * Route yang tetap boleh diakses akun admin-only,
     * selain halaman manajemen user (admin.*).
     */
    protected array $allowedRoutes = [
        'admin.*',
        'password.force-change',
        'password.force-change.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_admin && ! $request->routeIs(...$this->allowedRoutes)) {
            return redirect()->route('admin.users.index');
        }

        return $next($request);
    }
}
