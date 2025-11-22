<?php

namespace App\Enums\Notifications;

use App\Enums\BaseEnum;

/**
 * @method static static NONE()
 * @method static static TO_DEVICE_CANCEL_SUBSCRIPTION() // перенаправить на список девайсов со статусом cancel_subscription
 * @method static static TO_DEVICE_ACTIVE_TILL()        // перенаправить на список девайсов в состоянии деактивации
 */

class NotificationAction extends BaseEnum
{
    public const NONE  = 'none';
    public const TO_DEVICE_CANCEL_SUBSCRIPTION  = 'to_device_cancel_subscription';
    public const TO_DEVICE_ACTIVE_TILL  = 'to_device_active_till';
}


