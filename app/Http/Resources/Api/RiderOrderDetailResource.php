<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderOrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array for rider order detail view.
     */
    public function toArray(Request $request): array
    {
        /** @var Order $this */
        $status = (string) $this->status;
        $seller = $this->seller;

        $sellerImage = $seller?->profile_image;
        if ($sellerImage && !str_starts_with($sellerImage, 'http://') && !str_starts_with($sellerImage, 'https://')) {
            $sellerImage = asset('storage/' . ltrim($sellerImage, '/'));
        } elseif (!$sellerImage && $seller) {
            $sellerImage = $seller->display_profile_image ?? null;
        }

        // Subtotal calculated from items snapshot
        $subtotal = $this->relationLoaded('items')
            ? $this->items->sum(fn ($i) => ((float) $i->price) * ((int) $i->quantity))
            : (float) $this->items()->get()->sum(fn ($i) => ((float) $i->price) * ((int) $i->quantity));

        $proofImage = null;
        if ($this->delivery_proof_image) {
            $proof = $this->delivery_proof_image;
            $proofImage = (!str_starts_with($proof, 'http://') && !str_starts_with($proof, 'https://'))
                ? asset('storage/' . ltrim($proof, '/'))
                : $proof;
        }

        $canPickup = ($status === 'assigned_to_rider');
        $canDeliver = ($status === 'picked_up');

        $isDeliveredOrCompleted = in_array($status, ['delivered', 'completed'], true);
        $customerAreaHint = RiderAvailableOrderResource::extractAreaHint($this->address, $this->user);

        $customerPhone = $isDeliveredOrCompleted ? null : (string) $this->phone;
        $customerAddress = $isDeliveredOrCompleted ? $customerAreaHint : (string) $this->address;

        return [
            'id' => (int) $this->id,
            'status' => $status,
            'status_label' => OrderListResource::statusLabel($status),
            'status_step' => OrderListResource::statusStep($status),
            'seller' => [
                'name' => (string) ($seller?->name ?? 'Bakala Express Merchant'),
                'image' => $sellerImage,
                'address' => (string) ($seller?->full_address ?? ''),
                'area' => $seller?->area ? (string) $seller->area : null,
                'sector' => $seller?->sector ? (string) $seller->sector : null,
                'near_areas' => is_array($seller?->near_areas) ? $seller->near_areas : [],
            ],
            'customer' => [
                'name' => (string) ($this->user?->name ?? 'Customer'),
                'phone' => $customerPhone,
                'address' => $customerAddress,
                'delivery_instructions' => $this->delivery_instructions ? (string) $this->delivery_instructions : null,
            ],
            'items' => RiderOrderItemResource::collection($this->items),
            'subtotal' => number_format((float) $subtotal, 2, '.', ''),
            'delivery_charges' => number_format((float) ($this->delivery_charges ?? 0), 2, '.', ''),
            'discount_amount' => number_format((float) ($this->discount_amount ?? 0), 2, '.', ''),
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'amount_to_collect' => number_format((float) $this->total_amount, 2, '.', ''),
            'timestamps' => [
                'created_at' => $this->created_at?->toIso8601String(),
                'estimated_delivery_at' => $this->estimated_delivery_at ? \Carbon\Carbon::parse($this->estimated_delivery_at)->toIso8601String() : null,
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
            'delivery_proof_image' => $proofImage,
            'can_pickup' => $canPickup,
            'can_deliver' => $canDeliver,
        ];
    }
}
