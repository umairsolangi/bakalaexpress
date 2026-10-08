<?php

namespace App\Jobs;

use App\Models\ShopProduct;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductPriceUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $globalProductId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // For large catalogs, chunking can be added here if needed,
        // but a single update query is usually highly optimized.
        ShopProduct::where('global_product_id', $this->globalProductId)
            ->whereNull('custom_price')
            ->update(['updated_at' => now()]);
    }
}
