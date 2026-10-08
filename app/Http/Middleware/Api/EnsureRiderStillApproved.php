<?php

namespace App\Http\Middleware\Api;

use App\Models\Rider;
use App\Services\Api\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRiderStillApproved
{
    /**
     * Handle an incoming request and ensure the authenticated user is currently an approved rider.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rider = $request->user();

        if (! $rider instanceof Rider) {
            return ApiResponse::error(
                'Unauthorized rider access.',
                'FORBIDDEN_ROLE',
                [],
                403
            );
        }

        $currentApproved = Rider::whereKey($rider->getKey())->value('is_approved');

        if ($currentApproved === null || (int) $currentApproved !== 1) {
            return ApiResponse::error(
                'Your rider account is not approved or has been suspended.',
                'FORBIDDEN_ROLE',
                [],
                403
            );
        }

        return $next($request);
    }
}
