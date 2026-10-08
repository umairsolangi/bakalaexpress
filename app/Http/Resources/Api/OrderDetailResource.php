<?php

namespace App\Http\Resources\Api;

use App\Models\Feedback;
use App\Models\Order;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Detailed order resource for show, placement, and cancellation responses.
 */
class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Order $this */
        $status = (string) $this->status;
        $statusLabel = OrderListResource::statusLabel($status);
        $statusStep = OrderListResource::statusStep($status);

        $canCancel = in_array($status, ['pending', 'confirmed_by_seller'], true);

        $hasFeedback = $this->relationLoaded('feedbacks')
            ? $this->feedbacks !== null
            : Feedback::where('order_id', $this->id)->exists();

        $canReview = in_array($status, ['delivered', 'completed'], true) && !$hasFeedback;

        // Subtotal calculated from items snapshot
        $subtotal = $this->relationLoaded('items')
            ? $this->items->sum(fn ($i) => ((float) $i->price) * ((int) $i->quantity))
            : $this->items()->get()->sum(fn ($i) => ((float) $i->price) * ((int) $i->quantity));

        // Delivery proof URL only visible on terminal delivery states
        $proofImage = null;
        if (in_array($status, ['delivered', 'completed'], true) && $this->delivery_proof_image) {
            $proof = $this->delivery_proof_image;
            $proofImage = (!str_starts_with($proof, 'http://') && !str_starts_with($proof, 'https://'))
                ? asset('storage/' . ltrim($proof, '/'))
                : $proof;
        }

        // Seller profile summary (safe attributes only)
        $seller = $this->seller;
        $sellerImage = $seller?->profile_image;
        if ($sellerImage && !str_starts_with($sellerImage, 'http://') && !str_starts_with($sellerImage, 'https://')) {
            $sellerImage = asset('storage/' . ltrim($sellerImage, '/'));
        }

        // Rider summary (only if assigned; safe attributes only)
        $rider = $this->rider;
        $riderData = null;
        if ($rider) {
            $riderData = [
                'id' => (int) $rider->id,
                'name' => (string) $rider->name,
                'vehicle_type' => (string) ($rider->vehicle_type ?? 'Motorcycle'),
            ];
        }

        // Promo code text lookup
        $promoText = null;
        if ($this->promo_code_id) {
            $promoText = PromoCode::find($this->promo_code_id)?->code;
        }

        return [
            'id' => (int) $this->id,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_step' => $statusStep,
            'can_cancel' => $canCancel,
            'can_review' => $canReview,
            'address' => (string) $this->address,
            'phone' => (string) $this->phone,
            'delivery_instructions' => $this->delivery_instructions ? (string) $this->delivery_instructions : null,
            'payment_method' => 'cod',
            'payment_method_label' => 'Cash on Delivery',
            'subtotal' => number_format((float) $subtotal, 2, '.', ''),
            'delivery_charges' => number_format((float) ($this->delivery_charges ?? 0), 2, '.', ''),
            'discount_amount' => number_format((float) ($this->discount_amount ?? 0), 2, '.', ''),
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'promo_code' => $promoText,
            'estimated_delivery_at' => $this->estimated_delivery_at ? \Carbon\Carbon::parse($this->estimated_delivery_at)->toIso8601String() : null,
            'cancelled_at' => $this->cancelled_at ? \Carbon\Carbon::parse($this->cancelled_at)->toIso8601String() : null,
            'cancellation_reason' => $this->cancellation_reason ? (string) $this->cancellation_reason : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'items' => OrderItemResource::collection($this->items),
            'seller' => [
                'id' => $seller ? (int) $seller->id : null,
                'name' => (string) ($seller?->name ?? 'Bakala Express Merchant'),
                'image' => $sellerImage,
            ],
            'rider' => $riderData,
            'delivery_proof_image' => $proofImage,
            'unread_messages' => (int) ($this->unread_messages ?? \App\Models\Message::where('order_id', $this->id)->where('sender_type', 'seller')->where('is_read', false)->count()),
        ];
    }
}
