<?php

namespace App\Http\Resources\Api;

use App\Models\UserProfileUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CustomerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $avatarPath = null;
        $profileUpdate = UserProfileUpdate::where('user_id', $this->id)->first();
        if ($profileUpdate && $profileUpdate->profile_image) {
            $avatarPath = url(Storage::disk('public')->url($profileUpdate->profile_image));
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'mobile' => $this->mobile,
            'address' => $this->address,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'sector' => $this->sector,
            'near_area' => $this->near_area,
            'avatar_url' => $avatarPath,
            'pickup_time' => $this->pickup_time,
            'is_verified' => (bool) $this->is_verified,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
