<?php

namespace App\Traits;

use App\Exceptions\ErrorsCode;

trait AssetData
{
    public static function assetFieldAll(array $data, string $field): void
    {
        static::assetFieldExist($data, $field);
        static::assetFieldNotNull($data, $field);
        static::assetFieldNotEmpty($data, $field);
    }

    // @todo написать тесты на этот метод
    public static function assetHasValue(array $data, string $field): void
    {
        if(self::checkFieldExist($data, $field)){
            static::assetFieldNotNull($data, $field);
            static::assetFieldNotEmpty($data, $field);
        }
    }

    public static function getPrettyValue(array $data, string $field)
    {
        if(self::checkFieldExist($data, $field)){
            if(empty($data[$field])){
                return null;
            }
            return $data[$field];
        }

        return null;
    }

    public static function checkFieldExist(array $data, string $field): bool
    {
        return array_key_exists($field, $data);
    }

    public static function assetFieldExist(array $data, string $field): void
    {
        if(!array_key_exists($field, $data)){
            throw new \InvalidArgumentException(
                __('error.field must exist', ['field' => __("validation.attributes.{$field}")]),
                ErrorsCode::BAD_REQUEST
            );
        }
    }

    public static function assetFieldNotNull(array $data, string $field): void
    {
        if(null === $data[$field]){
            throw new \InvalidArgumentException(
                __('error.field can\'t be null', ['field' => __("validation.attributes.{$field}")]),
                ErrorsCode::BAD_REQUEST
            );
        }
    }

    public static function assetFieldNotEmpty(array $data, string $field): void
    {
        if(empty($data[$field])){
            throw new \InvalidArgumentException(
                __('error.field can\'t be empty', ['field' => __("validation.attributes.{$field}")]),
                ErrorsCode::BAD_REQUEST
            );
        }
    }
}
