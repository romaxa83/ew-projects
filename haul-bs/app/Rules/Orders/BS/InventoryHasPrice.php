<?php

namespace App\Rules\Orders\BS;

use App\Models\Inventories\Inventory;
use Illuminate\Contracts\Validation\Rule;

class InventoryHasPrice implements Rule
{
    public function passes($attribute, $value): bool
    {
        $inventory = Inventory::find((int) $value);

        if (!$inventory) {
            return false;
        }

        return $inventory->price_retail > 0;
    }

    public function message(): string
    {
        return __('validation.custom.order.bs.inventory_has_price');
    }
}
