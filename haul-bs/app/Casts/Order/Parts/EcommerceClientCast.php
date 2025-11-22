<?php

namespace App\Casts\Order\Parts;

use App\Entities\Order\Parts\EcommerceClientEntity;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use JsonException;

class EcommerceClientCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get($model, string $key, $value, array $attributes): ?EcommerceClientEntity
    {
        if (!$value) return null;

        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        return EcommerceClientEntity::make($value);
    }

    /**
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if(!$value) return null;

        if (!$value instanceof EcommerceClientEntity) {
            throw new InvalidArgumentException('The given value is not an EcommerceClientEntity instance');
        }

        return $value->toJson();
    }
}
