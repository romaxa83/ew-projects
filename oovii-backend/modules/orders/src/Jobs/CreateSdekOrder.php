<?php

namespace WezomCms\Orders\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Services\SdekService;

class CreateSdekOrder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Order $order;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order->withoutRelations();
        $this->queue = config('queue.queues.sdek');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $service = resolve(SdekService::class);

        $service->createOrder($this->order, OrderStatus::NEW); // change rollback order status
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
