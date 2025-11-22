<?php

namespace App\Listeners\Orders\Dealer;

use App\Events\Orders\Dealer\ApprovedOrderEvent;
use App\Notifications\Orders\Dealer\SendApprovedOrderToDealerNotification;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendEmailToDealerAsApprovedOrderListener implements ShouldQueue
{
    public function handle(ApprovedOrderEvent $event): void
    {
        try {
            $order = $event->getOrder()
                ->load([
                'dealer',
                'items.product',
                'items.primary',
            ]);

            $email = $order->dealer->email->getValue();
            Notification::route('mail', $email)
                ->notify(new SendApprovedOrderToDealerNotification($order, $event->isChanged()))
            ;

            logger_info("SEND Email to a dealer as approved order [{$email}]");

        } catch (\Throwable $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }
}
