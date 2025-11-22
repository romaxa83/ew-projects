<?php

namespace App\Enums\Notifications;

use App\Enums\BaseEnum;

/**
 * @method static static GPS()
 * @method static static GPS_SUBSCRIPTION()
 * @method static static COMPANY_SUBSCRIPTION()
 */

class NotificationType extends BaseEnum
{
    public const GPS  = 'gps';
    public const GPS_SUBSCRIPTION  = 'gps_subscription';
    public const COMPANY_SUBSCRIPTION  = 'company_subscription';
}
