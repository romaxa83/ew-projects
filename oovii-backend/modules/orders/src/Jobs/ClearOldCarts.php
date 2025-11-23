<?php

namespace WezomCms\Orders\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\NeedClearOldHashesInterface;

class ClearOldCarts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $cart = resolve(CartInterface::class);

        if ($cart instanceof NeedClearOldHashesInterface) {
            $cart->clearOldHashes();
        }
    }
}
