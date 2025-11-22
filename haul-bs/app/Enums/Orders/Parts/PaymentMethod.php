<?php

namespace App\Enums\Orders\Parts;

use App\Foundations\Enums\Traits\Helpers;
use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\Label;
use App\Foundations\Enums\Traits\RuleIn;

/**
 * @method static string Online()
 * @method static string Zello()
 * @method static string Venmo()
 * @method static string Cashapp()
 * @method static string Check()
 * @method static string Cash()
 * @method static string ACH()
 * @method static string Wire()
 * @method static string Card()
 * @method static string PayPal()
 * @method static string Payment_on_pickup()
 */

enum PaymentMethod: string {

    use InvokableCases;
    use RuleIn;
    use Label;
    use Helpers;

    case Online = "online_payment";
    case Zello = "zelle";
    case Venmo = "venmo";
    case Cashapp = "cashapp";
    case Check = "check";
    case Cash = "cash";
    case ACH = "ach";
    case Wire = "wire";

    // ecommerce
    case PayPal = "paypal";
    case Payment_on_pickup = "payment_on_pickup";

    public static function forOnline(): array
    {
        return [self::Online, self::PayPal];
    }

    public static function ruleInForEcom()
    {
        $items = array_map(fn($case) => $case->value, self::useEcommerce());

        return 'in:' . implode(',', $items);
    }

    public static function useEcommerce(): array
    {
        return [
            self::Online,
            self::PayPal,
            self::Payment_on_pickup,
        ];
    }

    public static function forImmediately(): array
    {
        $tmp = [
            self::Online,
            self::PayPal,
            self::Zello,
            self::Venmo,
            self::Cashapp,
        ];

        $res = [];
        foreach ($tmp  as $k => $enum) {
            $res[$k] = [
                'value' => $enum->value,
                'label' => $enum->label()
            ];
        }

        return $res;
    }

    public static function forThen(): array
    {
        $tmp = [
            self::ACH,
            self::Wire,
            self::Check,
        ];

        $res = [];
        foreach ($tmp  as $k => $enum) {
            $res[$k] = [
                'value' => $enum->value,
                'label' => $enum->label()
            ];
        }

        return $res;
    }

    public static function forAddPayment(): array
    {
        $tmp = [
            self::ACH,
            self::Wire,
            self::Check,
            self::Cashapp,
            self::Cash,
            self::Venmo,
            self::Zello,
        ];

        $res = [];
        foreach ($tmp  as $k => $enum) {
            $res[$k] = [
                'value' => $enum->value,
                'label' => $enum->label()
            ];
        }

        return $res;
    }
}
