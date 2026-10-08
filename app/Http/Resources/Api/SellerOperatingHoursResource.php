<?php

namespace App\Http\Resources\Api;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerOperatingHoursResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Seller $this */
        return [
            'is_open' => (bool) $this->is_open,
            'opens_at' => $this->opens_at ? substr((string) $this->opens_at, 0, 5) : null,
            'closes_at' => $this->closes_at ? substr((string) $this->closes_at, 0, 5) : null,
            'is_currently_open' => $this->isAcceptingOrders(),
        ];
    }
}
