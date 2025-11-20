<?php

namespace WezomCms\Users\Types;

final class LoyaltyLevel
{
    const NONE     = 0;
    const WHITE    = 1;
    const BLACK    = 2;
    const SILVER   = 3;
    const GOLD     = 4;
    const PLATINUM = 5;

    private function __construct(){}

    public static function forSelect(): array
    {
        return [
            self::NONE => __('cms-users::admin.loyalty-level.none'),
            self::WHITE => __('cms-users::admin.loyalty-level.white'),
            self::BLACK => __('cms-users::admin.loyalty-level.black'),
            self::SILVER => __('cms-users::admin.loyalty-level.silver'),
            self::GOLD => __('cms-users::admin.loyalty-level.gold'),
            self::PLATINUM => __('cms-users::admin.loyalty-level.platinum'),
        ];
    }

    public static function getName($level)
    {
        if(array_key_exists($level, self::forSelect())){
            return self::forSelect()[$level];
        }

        return null;
    }

}

