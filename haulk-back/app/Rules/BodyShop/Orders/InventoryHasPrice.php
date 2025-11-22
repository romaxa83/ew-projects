<?php

namespace App\Rules\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Contracts\Validation\Rule;

class InventoryHasPrice implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $inventory = Inventory::find((int) $value);

        if (!$inventory) {
            return false;
        }

        return $inventory->price_retail > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('The Price field of Inventory must be filled');
    }
}
