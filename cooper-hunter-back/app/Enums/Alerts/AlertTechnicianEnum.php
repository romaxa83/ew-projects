<?php

namespace App\Enums\Alerts;

use Core\Enums\BaseEnum;

/**
 * Class AlertTechnicianEnum
 * @package App\Enums\Alerts
 *
 * @method static static MODERATION_READY()
 * @method static static RE_MODERATION()
 * @method static static NEW_RE_MODERATION()
 * @method static static EMAIL_VERIFICATION_PROCESS()
 * @method static static EMAIL_VERIFICATION_READY()
 * @method static static REGISTRATION()
 * @method static static CUSTOM()
 */
final class AlertTechnicianEnum extends BaseEnum
{
    public const MODERATION_READY = 'moderation_ready';
    public const RE_MODERATION = 're_moderation';
    public const NEW_RE_MODERATION = 'new_re_moderation';
    public const EMAIL_VERIFICATION_PROCESS = 'email_verification_process';
    public const EMAIL_VERIFICATION_READY = 'email_verification_ready';
    public const REGISTRATION = 'registration';
    public const CUSTOM = 'custom';

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
            self::NEW_RE_MODERATION,
            self::REGISTRATION
        ];
    }
}
