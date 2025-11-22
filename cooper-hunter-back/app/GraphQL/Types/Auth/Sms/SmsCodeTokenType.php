<?php

namespace App\GraphQL\Types\Auth\Sms;

class SmsCodeTokenType extends BaseSmsToken
{
    public const NAME = 'SmsCodeTokenType';

    public const DESCRIPTION = "Токен выдается при запросе на авторизацию (подтверждение) номера телефона.
    Нужен для отправки с кодом из смс.
    В случае успешной проверки, будет выдан 'SmsAccessToken'";
}
