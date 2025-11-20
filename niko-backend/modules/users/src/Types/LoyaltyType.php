<?php

namespace WezomCms\Users\Types;

final class LoyaltyType
{
    const NONE       = 0;
    const INDIVIDUAL = 1;
    const FAMILY     = 2;

    private function __construct(){}

    public static function forSelect(): array
    {
        return [
            self::NONE => __('cms-users::admin.loyalty.none'),
            self::INDIVIDUAL => __('cms-users::admin.loyalty.individual'),
            self::FAMILY => __('cms-users::admin.loyalty.family'),
        ];
    }

    public static function hasType($type)
    {
        return $type == self::INDIVIDUAL || $type == self::FAMILY;
    }

    public static function getName($type)
    {
        if(array_key_exists($type, self::forSelect())){
            return self::forSelect()[$type];
        }

        return null;
    }
}
