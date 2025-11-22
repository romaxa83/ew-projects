<?php

namespace App\Casts\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class WarrantyStatusCast implements CastsAttributes
{
    /**
     * @throws InvalidEnumMemberException
     */
    public function get($model, string $key, $value, array $attributes): WarrantyStatus
    {
        return new WarrantyStatus($attributes['warranty_status']);
    }

    /**
     * @throws InvalidEnumMemberException
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        if (is_string($value)) {
            $value = new WarrantyStatus($value);
        }

        if (!$value instanceof WarrantyStatus) {
            throw new InvalidArgumentException(__('Incorrect Warranty status'));
        }

        return (string)$value;
    }
}
