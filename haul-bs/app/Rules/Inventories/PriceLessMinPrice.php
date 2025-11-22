<?php

namespace App\Rules\Inventories;

use App\Http\Requests\Inventories\Inventory\InventoryRequest;
use Illuminate\Contracts\Validation\Rule;

// цена товара не может быть меньшее минимальной цены товара
class PriceLessMinPrice implements Rule
{
    protected $priceMin = null;
    protected $price = null;

    public function __construct(
        protected InventoryRequest $request
    )
    {}

    public function passes($attribute, $value): bool
    {
        if(is_null($value)) return true;

        $this->priceMin = round($value, 2);

        if(!is_numeric($this->request['price_retail'])) return false;

        $this->price = round($this->request['price_retail'] ?? 0, 2);

        logger_info('PriceLessMinPrice', [
            'priceMin' => $this->priceMin,
            'price' => $this->price,
            'eq' => $this->priceMin < $this->price,
        ]);

        return $this->priceMin < $this->price;
    }

    public function message(): string
    {
        return __("validation.custom.inventory.price_less_min_price", [
            'price' => $this->price,
            'min_price' => $this->priceMin,
        ]);
    }
}

