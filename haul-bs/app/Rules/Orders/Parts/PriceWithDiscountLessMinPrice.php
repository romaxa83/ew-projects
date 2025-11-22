<?php

namespace App\Rules\Orders\Parts;

use App\Http\Requests\Orders\Parts\OrderPartsItemRequest;
use App\Models\Inventories\Inventory;
use Illuminate\Contracts\Validation\Rule;

// цена товара со скидкой не может быть меньше min_limit_price
class PriceWithDiscountLessMinPrice implements Rule
{
    protected $priceMinLimit = null;
    protected $priceWithDiscount = null;

    public function __construct(
        protected OrderPartsItemRequest $request
    )
    {}

    public function passes($attribute, $value): bool
    {
        if($value == 0) return true;

        $inventoryId = $this->request['inventory_id'] ?? null;
        $inventory = Inventory::query()
            ->select(['price_retail', 'min_limit_price'])
            ->where('id', $inventoryId)
            ->toBase()
            ->first();

        if(is_null($inventory->min_limit_price)) return true;

        $priceWithDiscount = price_with_discount(
            price: $inventory->price_retail,
            discount: $value
        );

        $this->priceMinLimit = $inventory->min_limit_price;
        $this->priceWithDiscount = $priceWithDiscount;

        return round($this->priceMinLimit, 2) < round($this->priceWithDiscount, 2);
    }

    public function message(): string
    {
        return __("validation.custom.order.parts.discounted_price_cannot_be_less_than_min_price", [
            'discounted_price' => $this->priceWithDiscount,
            'min_price' => $this->priceMinLimit,
        ]);
    }
}

