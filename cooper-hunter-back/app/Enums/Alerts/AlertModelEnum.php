<?php

namespace App\Enums\Alerts;

use App\Models\Dealers\Dealer;
use App\Models\Orders\Order;
use App\Models\Projects\System;
use App\Models\Support\SupportRequest;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Core\Enums\BaseEnum;

/**
 * Class AlertModelEnum
 * @package App\Enums\Alerts
 *
 * @method static static ORDER()
 * @method static static SYSTEM()
 * @method static static SUPPORT_REQUEST()
 * @method static static TECHNICIAN()
 * @method static static USER()
 * @method static static DEALER()
 */
final class AlertModelEnum extends BaseEnum
{
    public const ORDER = Order::MORPH_NAME;
    public const SYSTEM = System::MORPH_NAME;
    public const SUPPORT_REQUEST = SupportRequest::MORPH_NAME;
    public const TECHNICIAN = Technician::MORPH_NAME;
    public const USER = User::MORPH_NAME;
    public const DEALER = Dealer::MORPH_NAME;
}
