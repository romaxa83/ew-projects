<?php

namespace App\Casts\Warranty;

use App\Entities\Warranty\WarrantyUserInfo;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use JsonException;

class WarrantyUserInfoCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get($model, string $key, $value, array $attributes): WarrantyUserInfo
    {
        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        return WarrantyUserInfo::make($value, $value['is_user']);
    }

    /**
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        if (!$value instanceof WarrantyUserInfo) {
            throw new InvalidArgumentException('The given value is not an WarrantyUserInfo instance');
        }

        return $value->toJson();
    }
}
