<?php

namespace App\Enums\Orders;

use App\Foundations\Enums\Traits\InvokableCases;

/**
 * @method static string BS()
 * @method static string Parts()
 */
enum OrderType: string {

    use InvokableCases;

    case BS = "bs";
    case Parts = "parts";

    public function isBs(): bool
    {
        return $this === self::BS;
    }

    public function isParts(): bool
    {
        return $this === self::Parts;
    }
}
