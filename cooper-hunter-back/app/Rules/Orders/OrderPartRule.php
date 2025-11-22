<?php

namespace App\Rules\Orders;

use App\Models\Orders\Categories\OrderCategory;
use Illuminate\Contracts\Validation\Rule;

class OrderPartRule implements Rule
{
    public function __construct(
        protected string $idAttribute = 'id',
        protected string $key = 'id',
    ) {
    }

    public function passes($attribute, $value): bool
    {
        $id = (int)data_get($value, $this->idAttribute);
        $description = (string)data_get($value, 'description');

        if (empty($id)) {
            return true;
        }

        $orderCategory = OrderCategory::query()
            ->where($this->key, $id)
            ->first();

        if (!$orderCategory) {
            return true;
        }

        $needDescription = $orderCategory->need_description;

        return (!empty($description) && $needDescription) || (empty($description) && !$needDescription);
    }

    public function message(): string
    {
        return __('validation.custom.order.order_part_incorrect_description');
    }
}
