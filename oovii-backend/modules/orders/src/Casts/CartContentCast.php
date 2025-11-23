<?php

namespace WezomCms\Orders\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use WezomCms\Orders\Contracts\CartItemInterface;
use WezomCms\Orders\Models\CartItemContent;

class CartContentCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return CartItemContent::byArgs($this->getValue($value));
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        return ($value instanceof CartItemInterface)
            ? $value->toJson()
            : null;
    }

    protected function getValue($value): array
    {
        return json_decode($value, true) ?? [];
    }
}
