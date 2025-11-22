<?php

namespace App\Foundations\Casts\Locations;

use App\Foundations\Entities\Locations\AddressEntity;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use JsonException;

class AddressCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get($model, string $key, $value, array $attributes): ?AddressEntity
    {
        if (!$value) return null;

        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        return AddressEntity::make($value);
    }

    /**
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if(!$value) return null;

        if (!$value instanceof AddressEntity) {
            throw new InvalidArgumentException('The given value is not an AddressEntity instance');
        }

        return $value->toJson();
    }
}
