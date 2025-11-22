<?php

namespace App\Types;

final class Communication extends AbstractType
{
    const TELEGRAM = 'telegram';
    const VIBER = 'viber';
    const PHONE = 'phone';

    public static function list(): array
    {
        return [
            self::TELEGRAM => __('translation.communication.telegram'),
            self::VIBER => __('translation.communication.viber'),
            self::PHONE => __('translation.communication.phone')
        ];
    }

    protected static function exceptionMessage(array $replace = []): string
    {
        return __('error.not valid communication type', $replace);
    }
}
