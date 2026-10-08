<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'profile_image' => $this->profile_image,
            'city' => $this->city,
            'area' => $this->area,
            'sector' => $this->sector,
            'near_areas' => $this->near_areas,
            'full_address' => $this->full_address,
            'opens_at' => $this->opens_at,
            'closes_at' => $this->closes_at,
            'is_open' => (bool) $this->is_open,
            'catalog_category_id' => $this->catalog_category_id,
            'accountIsApproved' => (int) $this->accountIsApproved,
            'is_deleted' => (bool) $this->is_deleted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
