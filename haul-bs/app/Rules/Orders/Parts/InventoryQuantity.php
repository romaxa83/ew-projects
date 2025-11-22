<?php

namespace App\Rules\Orders\Parts;

use App\Http\Requests\Orders\Parts\Ecom\OrderPartsEcomRequest;
use App\Http\Requests\Orders\Parts\OrderPartsItemRequest;
use App\Http\Requests\Orders\Parts\OrderPartsRequest;
use App\Models\Inventories\Inventory;
use Illuminate\Contracts\Validation\Rule;

class InventoryQuantity implements Rule
{
    public function __construct(
        protected OrderPartsRequest|OrderPartsItemRequest|OrderPartsEcomRequest $request
    )
    {}

    public function passes($attribute, $value): bool
    {
        if($this->request instanceof OrderPartsItemRequest){
            $inventoryId = $this->request['inventory_id'] ?? null;
        } else {
            $tmp = explode('.', $attribute);
            $inventoryId = $this->request[$tmp[0]][$tmp[1]]['inventory_id'] ?? null;
        }

        if(!$inventoryId) return false;

        $inventoryQty = Inventory::query()->select(['quantity'])->where('id', $inventoryId)->toBase()->first();

        return $inventoryQty->quantity >= $value;
    }

    public function message(): string
    {
        return __("validation.custom.order.parts.few_quantities");
    }
}

