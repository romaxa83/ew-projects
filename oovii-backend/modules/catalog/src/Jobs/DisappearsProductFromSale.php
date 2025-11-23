<?php

namespace WezomCms\Catalog\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WezomCms\Catalog\Models\Product;

class DisappearsProductFromSale
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Product::where('sale', true)
            ->where('expires_at', '<=', now())
            ->update([
                'sale' => false,
                'expires_at' => null,
                'discount_percentage' => null,
                'cost' => \DB::raw('`old_cost`'),
                'old_cost' => null,
            ]);
    }
}
