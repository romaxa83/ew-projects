<?php

namespace App\Listeners\Orders\Dealer;

use App\Events\Orders\Dealer\UpdatePackingSlipEvent;
use App\Services\OneC\RequestService;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDataToOnecListener implements ShouldQueue
{
    public function handle(UpdatePackingSlipEvent $event): void
    {
        try {
            /** @var $service RequestService */
            $service = resolve(RequestService::class);
            $service->updateDealerOrderPackingSlip($event->getPackingSlip());

        } catch (\Throwable $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }
}
