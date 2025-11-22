<?php

namespace App\Listeners\Payments;

use App\Events\Payments\AddPaymentCardToMemberEvent;
use App\Events\Payments\DeletePaymentCardFromMemberEvent;
use App\Services\OneC\RequestService;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentCardToOnecListeners implements ShouldQueue
{
    public function handle(
        AddPaymentCardToMemberEvent|DeletePaymentCardFromMemberEvent $event
    ): void
    {
        try {
            /** @var $service RequestService */
            $service = resolve(RequestService::class);

            if($event instanceof AddPaymentCardToMemberEvent){
                if($event->getPaymentCard()->member->guid){
                    $service->addPaymentCard(
                        $event->getPaymentCard(),
                        $event->getDto()
                    );
                }
            }
            if($event instanceof DeletePaymentCardFromMemberEvent) {
                if($event->getPaymentCard()->guid){
                    $service->deletePaymentCard($event->getPaymentCard());
                }
            }


        } catch (\Throwable $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }
}



