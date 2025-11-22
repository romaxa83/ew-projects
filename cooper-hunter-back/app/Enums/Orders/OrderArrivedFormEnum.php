<?php

namespace App\Enums\Orders;

use Core\Enums\BaseEnum;

/**
 * @method static static DEALER()
 * @method static static TECH()
 */
final class OrderArrivedFormEnum extends BaseEnum
{
    public const DEALER = 'wp_dealers';
    public const TECH   = 'wp_techSupport';
}

