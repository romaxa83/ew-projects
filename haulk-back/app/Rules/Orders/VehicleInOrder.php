<?php

namespace App\Rules\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Vehicle;
use Illuminate\Contracts\Validation\Rule;

class VehicleInOrder implements Rule
{

    private Order $order;

    /**
     * Create a new rule instance.
     * @param Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Vehicle  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return $value->order_id === $this->order->id;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('Vehicle not found.');
    }
}
