<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderResource extends JsonResource
{
    protected bool $includeSensitive = false;

    /**
     * Set whether this resource is being returned for the rider's own authenticated profile endpoint.
     */
    public function withSensitiveFields(bool $include = true): self
    {
        $this->includeSensitive = $include;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_number' => $this->vehicle_number,
            'address' => $this->address,
            'status' => $this->status,
            'is_approved' => (bool) $this->is_approved,
            'is_verified' => (bool) $this->is_verified,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // In rider's own profile, expose only masked CNIC (e.g. "*****1234").
        // Document storage paths are NEVER exposed in any API response.
        if ($this->includeSensitive && ! empty($this->cnic_number)) {
            $lastFour = substr((string) $this->cnic_number, -4);
            $data['cnic_number'] = '*****' . $lastFour;
        }

        return $data;
    }
}
