<?php

namespace App\Http\Resources\Api;

use App\Models\Message;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Message $this */
        $user = $request->user();
        $isSeller = str_contains($request->path(), 'seller')
            || ($user instanceof Seller)
            || ($user?->tokenCan('seller'));

        $mySenderType = $isSeller ? 'seller' : 'user';
        $mine = ($this->sender_type === $mySenderType);

        $order = $this->relationLoaded('order') ? $this->order : $this->order()->with(['user', 'seller'])->first();

        if ($this->sender_type === 'user') {
            $fullName = (string) ($order?->user?->name ?? 'Customer');
            $nameParts = explode(' ', trim($fullName));
            $senderName = $nameParts[0] !== '' ? $nameParts[0] : $fullName;
        } else {
            $senderName = (string) ($order?->seller?->shop_name ?? $order?->seller?->name ?? 'Shop');
        }

        return [
            'id' => (int) $this->id,
            'mine' => (bool) $mine,
            'sender_name' => $senderName,
            'message' => (string) $this->message,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
