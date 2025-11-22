<?php


namespace App\Events\Payments;


use App\Models\Payments\PayPalCheckout;

class PayPalCheckoutSavedEvent
{
    public function __construct(private PayPalCheckout $checkout)
    {
    }

    public function getCheckout(): PayPalCheckout
    {
        return $this->checkout;
    }
}
