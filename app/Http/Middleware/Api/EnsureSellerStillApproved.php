<?php

namespace App\Http\Middleware\Api;

use App\Models\Seller;
use App\Services\Api\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerStillApproved
{
    /**
     * Handle an incoming request and ensure the authenticated user is currently an approved, non-deleted seller.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $seller = $request->user();

        if (! $seller instanceof Seller) {
            return ApiResponse::error(
                'Unauthorized seller access.',
                'FORBIDDEN_ROLE',
                [],
                403
            );
        }

        $sellerData = Seller::whereKey($seller->getKey())->first(['accountIsApproved', 'is_deleted']);

        if (! $sellerData || (int) $sellerData->accountIsApproved !== 1 || (int) $sellerData->is_deleted !== 0) {
            return ApiResponse::error(
                'Your seller account is not approved or has been deactivated.',
                'SELLER_ACCOUNT_INACTIVE',
                [],
                403
            );
        }

        return $next($request);
    }
}
