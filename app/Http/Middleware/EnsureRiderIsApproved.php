<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRiderIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rider = Auth::guard('rider')->user();

        if (!$rider || !$rider->is_approved) {
            if ($request->expectsJson() || $request->is('api/*')) {
                abort(403, 'Your rider account is not approved.');
            }

            return redirect()->route('rider.login')
                ->with('error', 'Your rider account is pending admin approval.');
        }

        return $next($request);
    }
}
