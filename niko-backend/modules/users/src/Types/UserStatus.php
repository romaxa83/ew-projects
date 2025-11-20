<?php

namespace WezomCms\Users\Types;

final class UserStatus
{
    const CREATED_NOT_VERIFY  = 0;
    const CREATED_VERIFY      = 1;

    private function __construct(){}

    public static function isVerify($status): bool
    {
        return $status == self::CREATED_VERIFY;
    }
}


