<?php

namespace App\Http\Middleware;

use App\Http\Controllers\LocationDetectorController;
use Closure;
use Illuminate\Http\Request;

class DetectVpnMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $countryCurrency = LocationDetectorController::detectVpn($request, $next)) {
            return redirect('pages/forbidden');
        }
        $request->merge([ 'countryCurrency' => $countryCurrency ]);
        return $next($request);
    }
}
