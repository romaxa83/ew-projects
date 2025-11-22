<?php

namespace App\Enums\Payments;

use Core\Enums\BaseEnum;

/**
 * Class PaymentReturnPlatformEnum
 * @package App\Enums\Payments
 *
 * @method static static WEB()
 * @method static static ANDROID()
 * @method static static IOS()
 */
final class PaymentReturnPlatformEnum extends BaseEnum
{
    public const WEB = 'web';
    public const ANDROID = 'android';
    public const IOS = 'ios';

}
