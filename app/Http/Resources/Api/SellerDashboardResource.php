<?php

namespace App\Http\Resources\Api;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerDashboardResource extends JsonResource
{
    protected array $stats;

    public function __construct($resource, array $stats = [])
    {
        parent::__construct($resource);
        $this->stats = $stats;
    }

    public function toArray(Request $request): array
    {
        /** @var Seller $this */
        $profileImage = $this->profile_image;
        if ($profileImage && !str_starts_with($profileImage, 'http://') && !str_starts_with($profileImage, 'https://')) {
            $profileImage = asset('storage/' . ltrim($profileImage, '/'));
        }

        return [
            'store' => [
                'id' => (int) $this->id,
                'name' => (string) $this->name,
                'email' => (string) $this->email,
                'city' => (string) $this->city,
                'area' => (string) $this->area,
                'sector' => (string) $this->sector,
                'full_address' => (string) $this->full_address,
                'profile_image' => $profileImage,
                'is_open' => (bool) $this->is_open,
                'opens_at' => $this->opens_at ? substr((string) $this->opens_at, 0, 5) : null,
                'closes_at' => $this->closes_at ? substr((string) $this->closes_at, 0, 5) : null,
                'is_currently_open' => $this->isAcceptingOrders(),
            ],
            'order_badges' => [
                'pending' => (int) ($this->stats['pending'] ?? 0),
                'confirmed' => (int) ($this->stats['confirmed'] ?? 0),
                'preparing' => (int) ($this->stats['preparing'] ?? 0),
                'ready' => (int) ($this->stats['ready'] ?? 0),
                'active_total' => (int) ($this->stats['active_total'] ?? 0),
                'delivered' => (int) ($this->stats['delivered'] ?? 0),
                'completed' => (int) ($this->stats['completed'] ?? 0),
                'cancelled' => (int) ($this->stats['cancelled'] ?? 0),
                'rejected' => (int) ($this->stats['rejected'] ?? 0),
            ],
            'today' => [
                'sales' => number_format((float) ($this->stats['today_sales'] ?? 0), 2, '.', ''),
                'orders_count' => (int) ($this->stats['today_orders_count'] ?? 0),
            ],
        ];
    }
}
