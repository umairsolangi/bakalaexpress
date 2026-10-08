<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerOrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Order $this */
        $status = (string) $this->status;
        $statusLabel = OrderListResource::statusLabel($status);
        $statusStep = OrderListResource::statusStep($status);

        // Allowed seller actions based on order lifecycle
        $allowedActions = match ($status) {
            'pending' => ['confirm', 'reject'],
            'confirmed_by_seller' => ['prepare', 'reject'],
            'preparing' => ['ready', 'reject'],
            'delivered' => ['complete'],
            default => [],
        };

        // Subtotal calculated from items snapshot
        $subtotal = $this->relationLoaded('items')
            ? $this->items->sum(fn ($i) => ((float) $i->price) * ((int) $i->quantity))
            : $this->items()->get()->sum(fn ($i) => ((float) $i->price) * ((int) $i->quantity));

        // Delivery proof URL
        $proofImage = null;
        if ($this->delivery_proof_image) {
            $proof = $this->delivery_proof_image;
            $proofImage = (!str_starts_with($proof, 'http://') && !str_starts_with($proof, 'https://'))
                ? asset('storage/' . ltrim($proof, '/'))
                : $proof;
        }

        // Rider summary (if assigned)
        $rider = $this->rider;
        $riderData = null;
        if ($rider) {
            $riderData = [
                'id' => (int) $rider->id,
                'name' => (string) $rider->name,
                'phone' => (string) $rider->phone,
                'vehicle_type' => (string) ($rider->vehicle_type ?? 'Motorcycle'),
            ];
        }

        // Promo text lookup
        $promoText = null;
        if ($this->promo_code_id) {
            $promoText = PromoCode::find($this->promo_code_id)?->code;
        }

        // Customer details
        $customerName = (string) ($this->user?->name ?? $this->name ?? 'Customer');
        $customerData = [
            'name' => $customerName,
            'phone' => (string) $this->phone,
            'address' => (string) $this->address,
            'delivery_instructions' => $this->delivery_instructions ? (string) $this->delivery_instructions : null,
        ];

        // Format items with current stock info for the merchant
        $items = $this->items->map(function ($item) {
            $imageUrl = $item->item_image;
            if ($imageUrl && !str_starts_with($imageUrl, 'http://') && !str_starts_with($imageUrl, 'https://')) {
                $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
            }

            $currentStock = null;
            if ($item->shop_product_id) {
                $currentStock = $item->shopProduct ? (int) $item->shopProduct->stock_quantity : null;
            }

            $unitPrice = (float) $item->price;
            $qty = (int) $item->quantity;
            $lineTotal = round($unitPrice * $qty, 2);

            return [
                'id' => (int) $item->id,
                'shop_product_id' => $item->shop_product_id ? (int) $item->shop_product_id : null,
                'is_catalog_item' => ($item->shop_product_id !== null),
                'item_name' => (string) $item->item_name,
                'unit_type' => $item->unit_type ? (string) $item->unit_type : null,
                'item_image' => $imageUrl,
                'quantity' => $qty,
                'current_stock' => $currentStock,
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'line_total' => number_format($lineTotal, 2, '.', ''),
            ];
        });

        return [
            'id' => (int) $this->id,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_step' => $statusStep,
            'allowed_actions' => $allowedActions,
            'customer' => $customerData,
            'rider' => $riderData,
            'items' => $items,
            'subtotal' => number_format((float) $subtotal, 2, '.', ''),
            'delivery_charges' => number_format((float) ($this->delivery_charges ?? 0), 2, '.', ''),
            'discount_amount' => number_format((float) ($this->discount_amount ?? 0), 2, '.', ''),
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'promo_code' => $promoText,
            'cancellation_reason' => $this->cancellation_reason ? (string) $this->cancellation_reason : null,
            'delivery_proof_image' => $proofImage,
            'inventory_reserved_at' => $this->inventory_reserved_at ? \Carbon\Carbon::parse($this->inventory_reserved_at)->toIso8601String() : null,
            'estimated_delivery_at' => $this->estimated_delivery_at ? \Carbon\Carbon::parse($this->estimated_delivery_at)->toIso8601String() : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'unread_messages' => (int) ($this->unread_messages ?? \App\Models\Message::where('order_id', $this->id)->where('sender_type', 'user')->where('is_read', false)->count()),
        ];
    }
}
