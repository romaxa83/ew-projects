<?php

namespace App\Dto\Orders;

use App\Dto\BaseDictionaryDto;
use App\Models\Orders\Deliveries\OrderDeliveryType;

/**
 * Class OrderDeliveryTypeDto
 * @package App\Dto\Orders
 *
 */
class OrderDeliveryTypeDto extends BaseDictionaryDto
{
    protected function getDefaultActive(): bool
    {
        return OrderDeliveryType::DEFAULT_ACTIVE;
    }
}

