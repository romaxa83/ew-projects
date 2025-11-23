<?php

namespace App\Enums\Orders;

use App\Enums\Base\InvokableCases;

/**
 * @method static Status()
 * @method static User()
 * @method static Division()
 * @method static Source()
 * @method static Move_size()
 * @method static Sizing_is_auto()
 * @method static Sizing_volume()
 * @method static Sizing_weight()
 * @method static Email()
 * @method static Sms()
 * @method static Order_customs_extras()
 * @method static Order_materials()
 */

enum ActivityType: string {

    use InvokableCases;

    case Status = "status";
    case User = "user";
    case Division = "division";
    case Source = "source";
    case Move_size = "move_size";
    case Sizing_is_auto = "sizing_is_auto";
    case Sizing_volume = "sizing_volume";
    case Sizing_weight = "sizing_weight";
    case Email = "email";
    case Sms = "sms";
    case Order_customs_extras = "order.customsExtras";
    case Order_materials = "order.materials";

    public static function supportCommunicationPanel(): array
    {
        return [
            self::Status->value,
            self::Division->value,
            self::User->value,
            self::Source->value,
            self::Email->value,
        ];
    }
}
