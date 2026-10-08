<?php

namespace App\Http\Middleware\Api;

use App\Services\Api\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminStillActive
{
    /**
     * Handle an incoming request and ensure the authenticated user is currently an active admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Admin access has been revoked.', 'FORBIDDEN_ROLE', [], 403);
        }

        $currentSellerType = \App\Models\User::whereKey($user->getKey())->value('sellerType');

        if ((int) $currentSellerType !== 1) {
            return ApiResponse::error(
                'Admin access has been revoked.',
                'FORBIDDEN_ROLE',
                [],
                403
            );
        }

        return $next($request);
    }
}
