<?php

namespace App\Contracts\Members;

use App\Models\Payments\PaymentCard;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface MemberPaymentCard
{
    public function cards(): MorphMany|PaymentCard;
}
