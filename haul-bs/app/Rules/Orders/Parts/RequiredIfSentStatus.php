<?php

namespace App\Rules\Orders\Parts;

use App\Http\Requests\Orders\Parts\OrderPartsChangeStatusRequest;
use App\Models\Orders\Parts\Order;
use Illuminate\Contracts\Validation\Rule;

class RequiredIfSentStatus implements Rule
{
    public function __construct(
        protected OrderPartsChangeStatusRequest $request
    )
    {}

    public function passes($attribute, $value): bool
    {
        dd($this->request);

        if(
            $value
            && $this->order->isDraft()
            && ($this->order->delivery_address == null || $this->order->items->count() < 1)
        ) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return __("validation.custom.order.parts.not_items_and_delivery_method");
    }
}
