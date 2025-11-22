<?php

namespace App\Rules\Orders;

use App\Models\Orders\Order;
use Illuminate\Contracts\Validation\Rule;

class IsNotLastVehicle implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Order  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return $value->vehicles->count() > 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('The order must contain at least one vehicle.');
    }
}
