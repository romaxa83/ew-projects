<?php

namespace App\Rules\Orders\BS;

use App\Http\Requests\Orders\BS\OrderRequest;
use App\Models\Inventories\Inventory;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class QuantityRule implements Rule
{
    private OrderRequest $request;

    public function __construct(OrderRequest $request)
    {
        $this->request = $request;
    }

    public function passes($attribute, $value): bool
    {
        $inventoryAttr = str_replace('quantity', 'id', $attribute);
        $inventoryId = (int) Arr::get($this->request->toArray(), $inventoryAttr);

        if (!$inventoryId) {
            return false;
        }

        $inventory = Inventory::find($inventoryId);

        if (!$inventory) {
            return false;
        }

        return $inventory->unit->accept_decimals || ($value - (int) $value) == 0;
    }

    public function message(): string
    {
        return trans('The quantity must be integer value.');
    }
}

