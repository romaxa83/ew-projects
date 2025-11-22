<?php

namespace App\Rules\Orders\Parts;

use App\Enums\Orders\Parts\DeliveryType;
use App\Models\Orders\Parts\Order;
use Illuminate\Contracts\Validation\Rule;

class HasOverloadRule implements Rule
{
    public function __construct(
        protected Order $order
    )
    {}

    public function passes($attribute, $value): bool
    {
        if(
            $value == DeliveryType::Delivery()
            && $this->order->hasOverloadInventory()
        ){
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return __("validation.custom.order.parts.has_overload");
    }
}
