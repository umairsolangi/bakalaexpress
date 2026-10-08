<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerEarningsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'summary' => [
                'today' => number_format((float) ($this->resource['today'] ?? 0), 2, '.', ''),
                'this_week' => number_format((float) ($this->resource['this_week'] ?? 0), 2, '.', ''),
                'this_month' => number_format((float) ($this->resource['this_month'] ?? 0), 2, '.', ''),
                'this_year' => number_format((float) ($this->resource['this_year'] ?? 0), 2, '.', ''),
                'all_time' => number_format((float) ($this->resource['all_time'] ?? 0), 2, '.', ''),
                'total_completed_orders' => (int) ($this->resource['total_completed_orders'] ?? 0),
            ],
            'monthly_chart' => $this->resource['monthly_chart'] ?? [],
            'discounts_note' => 'Total sales reflect the final order amounts after customer promo discounts, matching the web earnings calculation.',
        ];
    }
}
