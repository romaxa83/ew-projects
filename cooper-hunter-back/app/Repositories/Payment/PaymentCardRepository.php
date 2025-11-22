<?php

namespace App\Repositories\Payment;

use App\Models\Payments\PaymentCard;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class PaymentCardRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return PaymentCard::query();
    }
}
