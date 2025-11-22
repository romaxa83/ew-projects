<?php

namespace App\Enums\Inventories\Transaction;

use App\Foundations\Enums\Traits\InvokableCases;

enum OperationType: string {

    use InvokableCases;

    case PURCHASE = "purchase";
    case SOLD = "sold";

    public function isPurchase(): bool
    {
        return $this === self::PURCHASE;
    }

    public function isSold(): bool
    {
        return $this === self::SOLD;
    }
}
