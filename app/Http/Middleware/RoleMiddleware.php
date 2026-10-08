<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Enforce role-based route access across all guards.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (empty($roles)) {
            abort(403, 'No role configured for this route.');
        }

        $roles = array_map('strtolower', $roles);

        foreach ($roles as $role) {
            if ($this->isAuthorizedForRole($role)) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            abort(403, 'Forbidden');
        }

        return redirect($this->resolveDashboardForAuthenticatedUser())
            ->with('error', 'You are not authorized to access that area.');
    }

    private function isAuthorizedForRole(string $role): bool
    {
        return match ($role) {
            'admin' => Auth::guard('web')->check() && (int) (Auth::guard('web')->user()->sellerType ?? 0) === 1,
            'user' => Auth::guard('web')->check() && (int) (Auth::guard('web')->user()->sellerType ?? 0) !== 1,
            'seller' => Auth::guard('seller')->check(),
            'rider' => Auth::guard('rider')->check(),
            default => false,
        };
    }

    private function resolveDashboardForAuthenticatedUser(): string
    {
        if (Auth::guard('web')->check()) {
            return (int) (Auth::guard('web')->user()->sellerType ?? 0) === 1
                ? route('admin.dashboard')
                : route('home');
        }

        if (Auth::guard('seller')->check()) {
            return route('seller.panel');
        }

        if (Auth::guard('rider')->check()) {
            return route('rider.dashboard');
        }

        return route('login');
    }
}
