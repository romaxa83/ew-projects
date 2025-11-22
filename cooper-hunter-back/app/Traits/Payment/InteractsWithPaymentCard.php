<?php

namespace App\Traits\Payment;

use App\Models\Payments\PaymentCard;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithPaymentCard
{
    public function cards(): MorphMany
    {
        return $this->morphMany(PaymentCard::class, 'member');
    }
}
