<?php

namespace App\Rules\Orders\BS;

use App\Models\Orders\BS\Order;
use Illuminate\Contracts\Validation\Rule;

class HasEnoughInventoryForWholeOrder implements Rule
{
    public function __construct(protected Order $order)
    {}

    public function passes($attribute, $value): bool
    {
        $order = $this->order;

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

    public function message(): string
    {
        return __('validation.custom.order.bs.not_enough_inventory_for_restore');
    }
}
