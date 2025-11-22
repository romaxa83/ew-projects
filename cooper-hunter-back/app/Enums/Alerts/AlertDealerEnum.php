<?php

namespace App\Enums\Alerts;

use Core\Enums\BaseEnum;

/**
 * Class AlertDealerEnum
 * @package App\Enums\Alerts
 *
 * @method static static EMAIL_VERIFICATION_PROCESS()
 * @method static static EMAIL_VERIFICATION_READY()
 * @method static static REGISTRATION()
 */
final class AlertDealerEnum extends BaseEnum
{
    public const EMAIL_VERIFICATION_PROCESS = 'email_verification_process';
    public const EMAIL_VERIFICATION_READY = 'email_verification_ready';
    public const REGISTRATION = 'registration';

    public static function getFrontList(): array
    {
        return array_filter(
            self::getValues(),
            fn(string $item) => !in_array($item, self::getBackList())
        );
    }

    public static function getBackList(): array
    {
        return [
            self::REGISTRATION
        ];
    }
}

