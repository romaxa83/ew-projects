<?php

namespace App\Traits;

use App\Exceptions\AssertDataException;

trait AssertData
{
    /** @throws AssertDataException */
    public static function assetField(array $data, string $field): void
    {
        static::fieldExist($data, $field);
        static::fieldNotNull($data, $field);
        static::fieldNotEmpty($data, $field);
    }

    /** @throws AssertDataException */
    public static function fieldExist(array $data, string $field): void
    {
        if (!static::checkFieldExist($data, $field)) {
            throw new AssertDataException(
                __('exceptions.assert_data.field must exist', ['field' => $field])
            );
        }
    }

    public static function checkFieldExist(array $data, string $field): bool
    {
        return array_key_exists($field, $data);
    }

    /** @throws AssertDataException */
    public static function fieldNotNull(array $data, string $field): void
    {
        if (!static::checkFieldNotNull($data, $field)) {
            throw new AssertDataException(
                __('exceptions.assert_data.field not null', ['field' => $field])
            );
        }
    }

    public static function checkFieldNotNull(array $data, string $field): bool
    {
        return null !== $data[$field];
    }

    /** @throws AssertDataException */
    public static function fieldNotEmpty(array $data, string $field): void
    {
        if (!static::checkFieldNotEmpty($data, $field)) {
            throw new AssertDataException(
                __('exceptions.assert_data.field not empty', ['field' => $field])
            );
        }
    }

    public static function checkFieldNotEmpty(array $data, string $field): bool
    {
        return !empty($data[$field]);
    }
}

