<?php

namespace App\Types;

final class UserType extends AbstractType
{
    public const TYPE_PERSONAL = 1;    // физ. лицо
    public const TYPE_LEGAL    = 2;    // юр. лицо

    public static function list(): array
    {
        return [
            self::TYPE_PERSONAL => __('translation.user.type.personal'),
            self::TYPE_LEGAL => __('translation.user.type.legal'),
        ];
    }

    protected static function exceptionMessage(array $replace = []): string
    {
        return __('error.not valid user type', $replace);
    }
}
