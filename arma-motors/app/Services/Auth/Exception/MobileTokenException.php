<?php

namespace App\Services\Auth\Exception;

use App\Exceptions\ErrorsCode;

class MobileTokenException extends \Exception
{

    public static function throwIncorrectRefreshToken(): void
    {
        throw new self(__('error.mobile_token.incorrect refresh token'),ErrorsCode::MOBILE_TOKEN_INCORRECT_REFRESH_TOKEN);
    }

    public static function throwIncorrectDeviceId()
    {
        throw new self(__('error.mobile_token.incorrect device id'),ErrorsCode::MOBILE_TOKEN_INCORRECT_DEVICE_ID);
    }

    public static function throwNotEqualsDeviceId()
    {
        throw new self(__('error.mobile_token.not equals device id'),ErrorsCode::MOBILE_TOKEN_NOT_EQUALS_DEVICE_ID);
    }
}
