<?php

namespace App\Casts\Warranty;

use App\Entities\Warranty\WarrantyProductInfo;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use JsonException;

class WarrantyProductInfoCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get($model, string $key, $value, array $attributes): WarrantyProductInfo
    {
        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        return WarrantyProductInfo::make($value);
    }

    /**
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        if (!$value instanceof WarrantyProductInfo) {
            throw new InvalidArgumentException('The given value is not an WarrantyProductInfo instance');
        }

        return $value->toJson();
    }
}
