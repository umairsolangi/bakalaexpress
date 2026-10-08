<?php

namespace App\Http\Resources\Api;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderOrderItemResource extends JsonResource
{
    /**
     * Transform the order item into an array for the rider checklist.
     */
    public function toArray(Request $request): array
    {
        /** @var OrderItem $this */
        $name = $this->item_name
            ?? $this->product?->name
            ?? $this->globalProduct?->name
            ?? 'Product Item';

        $unitType = $this->unit_type
            ?? $this->product?->unit_type
            ?? $this->globalProduct?->unit
            ?? 'piece';

        return [
            'id' => (int) $this->id,
            'name' => (string) $name,
            'unit_type' => (string) $unitType,
            'quantity' => (int) $this->quantity,
        ];
    }
}
