<?php

namespace App\Rules\TypeOfWorks;

use App\Http\Requests\TypeOfWorks\TypeOfWorkRequest;
use App\Models\Inventories\Inventory;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class QuantityRule implements Rule
{
    private TypeOfWorkRequest $request;

    public function __construct(TypeOfWorkRequest $request)
    {
        $this->request = $request;
    }

    public function passes($attribute, $value): bool
    {
        $inventoryAttr = str_replace('quantity', 'id', $attribute);
        $inventoryId = Arr::get($this->request->toArray(), $inventoryAttr);

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
        return __('validation.custom.quantity.must_be_integer');
    }
}
