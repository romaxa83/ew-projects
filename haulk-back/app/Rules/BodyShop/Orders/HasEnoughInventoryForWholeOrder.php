<?php

namespace App\Rules\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use Illuminate\Contracts\Validation\Rule;

class HasEnoughInventoryForWholeOrder implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Order|null  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $order = Order::onlyTrashed()->find($value);

        if (!$order) {
            return true;
        }

        $requiredCount = [];
        foreach ($order->inventories as $inventory) {
            if (isset($requiredCount[$inventory->inventory_id])) {
                $requiredCount[$inventory->inventory_id] += $inventory->quantity;
            } else {
                $requiredCount[$inventory->inventory_id] = $inventory->quantity;
            }
        }

        foreach ($order->inventories as $inventory) {
            if ($inventory->inventory->quantity < $requiredCount[$inventory->inventory_id]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('Not enough inventory for this order. You will need to edit the order before restoring it.');
    }
}
