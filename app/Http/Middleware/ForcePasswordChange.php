<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Route yang tetap boleh diakses meski must_change_password masih true,
        // supaya user tidak terjebak redirect loop.
        $allowedRoutes = [
            'password.force-change',
            'password.force-change.update',
            'logout',
        ];

        if ($user && $user->must_change_password && ! $request->routeIs(...$allowedRoutes)) {
            return redirect()->route('password.force-change');
        }

        return $next($request);
    }
}
