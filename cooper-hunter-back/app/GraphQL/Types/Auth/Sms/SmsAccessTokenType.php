<?php

namespace App\GraphQL\Types\Auth\Sms;

class SmsAccessTokenType extends BaseSmsToken
{
    public const NAME = 'SmsAccessTokenType';

    public const DESCRIPTION = "Токен выдается при валидном 'SmsCodeToken'. Нужен для авторизации запроса / подтверждении телефона.";
}
