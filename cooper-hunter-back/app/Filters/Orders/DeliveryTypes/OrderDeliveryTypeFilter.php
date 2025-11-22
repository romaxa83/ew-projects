<?php

namespace App\Filters\Orders\DeliveryTypes;

use App\Filters\BaseDictionaryModelFilter;
use App\Models\Orders\Deliveries\OrderDeliveryType;

/**
 * Class OrderCategoryFilter
 * @package App\Filters\Orders\DeliveryTypes
 *
 */
class OrderDeliveryTypeFilter extends BaseDictionaryModelFilter
{
    public const TABLE = OrderDeliveryType::TABLE;
}
