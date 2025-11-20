<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait AssetData
{
    public static function assetFieldInArray(array $data, string $field): void
    {
        static::assetFieldExist($data, $field);
        static::assetFieldNotNull($data, $field);
        static::assetFieldNotEmpty($data, $field);
    }

    public static function checkFieldExist(array $data, string $field): bool
    {
        return array_key_exists($field, $data);
    }

    public static function assetFieldExist(array $data, string $field): void
    {
        if(!array_key_exists($field, $data)){
            throw new \InvalidArgumentException(
                    "field [{$field}] must exist",
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public static function assetFieldNotNull(array $data, string $field): void
    {
        if(null === $data[$field]){
            throw new \InvalidArgumentException(
                "field [{$field}] can\'t be null",
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public static function assetFieldNotEmpty(array $data, string $field): void
    {
        if(empty($data[$field])){
            throw new \InvalidArgumentException(
                "field [{$field}] can\'t be empty",
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
