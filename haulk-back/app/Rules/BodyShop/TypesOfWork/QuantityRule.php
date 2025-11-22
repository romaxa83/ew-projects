<?php

namespace App\Rules\BodyShop\TypesOfWork;

use App\Http\Requests\BodyShop\TypesOfWork\TypeOfWorkRequest;
use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\Models\Media;

class QuantityRule implements Rule
{
    private TypeOfWorkRequest $request;

    /**
     * Create a new rule instance.
     * @param TypeOfWorkRequest $request
     * @return void
     */
    public function __construct(TypeOfWorkRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Media|null  $value
     * @return bool
     */
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

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('The quantity must be integer value.');
    }
}
