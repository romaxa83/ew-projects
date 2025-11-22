<?php

namespace App\Services\Sms\Exceptions;

use App\Exceptions\ErrorsCode;
use Exception;

class SmsVerifyException extends Exception
{
    // запрос на генерацию action токена, но он еще активен
    public static function throwActiveActionToken()
    {
        throw new self(__('error.active action token'),ErrorsCode::ACTION_TOKEN_ACTIVE);
    }

    // запрос на генерацию sms токена, но он еще активен
    public static function throwActiveSmsToken()
    {
        throw new self(__('error.active sms token'),ErrorsCode::SMS_TOKEN_ACTIVE);
    }

}
