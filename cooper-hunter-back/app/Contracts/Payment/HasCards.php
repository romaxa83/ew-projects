<?php

namespace App\Contracts\Payment;

use App\Models\Payments\PaymentCard;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasCards
{
    public function cards(): MorphMany|PaymentCard;
}
