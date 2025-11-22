<?php

namespace Database\Factories\Orders\Deliveries;

use App\Models\Orders\Deliveries\OrderDeliveryType;
use Database\Factories\BaseDictionaryFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|OrderDeliveryType[]|OrderDeliveryType create(array $attributes = [])
 */
class OrderDeliveryTypeFactory extends BaseDictionaryFactory
{
    protected $model = OrderDeliveryType::class;
}
