<?php

declare(strict_types=1);

namespace Wezom\Quotes\Enums;

use Wezom\Core\Traits\EnumToArrayTrait;

enum QuoteStatusEnum: string
{
    use EnumToArrayTrait;

    case DRAFT = 'DRAFT';
    case NEW = 'NEW';
    case EXPIRED = 'EXPIRED';
    case SCHEDULED = 'SCHEDULED';
    case ARRIVED_AT_THE_PICKUP = 'ARRIVED_AT_THE_PICKUP';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DEPARTED_FROM_THE_PICKUP = 'DEPARTED_FROM_THE_PICKUP';
    case ARRIVED_AT_THE_DELIVERY = 'ARRIVED_AT_THE_DELIVERY';
    case DELIVERED = 'DELIVERED';
}
