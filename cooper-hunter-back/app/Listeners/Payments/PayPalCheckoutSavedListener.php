<?php


namespace App\Listeners\Payments;


use App\Events\Payments\PayPalCheckoutSavedEvent;
use App\Services\Payment\PayPalService;
use Illuminate\Contracts\Queue\ShouldQueue;

class PayPalCheckoutSavedListener implements ShouldQueue
{

    public function __construct(private PayPalService $payPalService)
    {
    }

    public function handle(PayPalCheckoutSavedEvent $event): void
    {
        $checkout = $event->getCheckout();

        if (!$checkout->wasChanged()) {
            return;
        }

        if ($checkout->wasChanged('checkout_status')) {
            $this->payPalService->checkChangeCheckoutStatus($checkout);
        }

        if ($checkout->wasChanged('refund_status')) {
            $this->payPalService->checkChangeRefundStatus($checkout);
        }
    }
}
