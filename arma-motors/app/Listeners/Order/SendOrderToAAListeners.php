<?php

namespace App\Listeners\Order;

use App\Events\Order\CreateOrder;
use App\Services\AA\RequestService;

class SendOrderToAAListeners
{
    public function __construct(protected RequestService $requestService)
    {}

    public function handle(CreateOrder $event)
    {
        try {
            if(isset($event->order) && (null !== $event->order)){
                $this->requestService->createOrder($event->order);
            }
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
