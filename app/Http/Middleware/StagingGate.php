namespace App\Http\Middleware;

use Closure;

class StagingGate
{
    public function handle($request, Closure $next)
    {
        if (app()->environment('staging') && ! $request->session()->get('staging_ok')) {
            if ($request->input('key') === env('STAGING_KEY')) {
                $request->session()->put('staging_ok', true);
                return $next($request);
            }
            abort(403, 'Butuh akses key untuk masuk ke sistem tahap pengembangan.');
        }
        return $next($request);
    }
}
