<?php

namespace App\Enums\Vehicles;

use App\Foundations\Enums\BaseEnum;
use Illuminate\Validation\Rule;

class VehicleType extends BaseEnum
{
    public const VEHICLE_TYPE_ATV = 1;
    public const VEHICLE_TYPE_ATV_NAME = 'ATV';
    public const VEHICLE_TYPE_BOAT = 2;
    public const VEHICLE_TYPE_BOAT_NAME = 'Boat';
    public const VEHICLE_TYPE_COUPE_2 = 3;
    public const VEHICLE_TYPE_COUPE_2_NAME = 'Coupe (2 doors)';
    public const VEHICLE_TYPE_FREIGHT = 4;
    public const VEHICLE_TYPE_FREIGHT_NAME = 'Freight';
    public const VEHICLE_TYPE_HEAVY_MACHINERY = 5;
    public const VEHICLE_TYPE_HEAVY_MACHINERY_NAME = 'Heavy Machinery';
    public const VEHICLE_TYPE_LIVESTOCK = 6;
    public const VEHICLE_TYPE_LIVESTOCK_NAME = 'Livestock';
    public const VEHICLE_TYPE_MOTORCYCLE = 7;
    public const VEHICLE_TYPE_MOTORCYCLE_NAME = 'Motorcycle';
    public const VEHICLE_TYPE_PICKUP_4 = 8;
    public const VEHICLE_TYPE_PICKUP_4_NAME = 'Pickup (4 Doors)';
    public const VEHICLE_TYPE_PICKUP_2 = 9;
    public const VEHICLE_TYPE_PICKUP_2_NAME = 'Pickup (2 Doors)';
    public const VEHICLE_TYPE_RV = 10;
    public const VEHICLE_TYPE_RV_NAME = 'RV';
    public const VEHICLE_TYPE_SEDAN = 11;
    public const VEHICLE_TYPE_SEDAN_NAME = 'Sedan';
    public const VEHICLE_TYPE_SUV = 12;
    public const VEHICLE_TYPE_SUV_NAME = 'SUV';
    public const VEHICLE_TYPE_TRAILER_BUMPER = 13;
    public const VEHICLE_TYPE_TRAILER_BUMPER_NAME = 'Trailer (Bumper Pull)';
    public const VEHICLE_TYPE_TRAILER_GOOSENECK = 14;
    public const VEHICLE_TYPE_TRAILER_GOOSENECK_NAME = 'Trailer (Gooseneck)';
    public const VEHICLE_TYPE_TRAILER_5_WHEEL = 15;
    public const VEHICLE_TYPE_TRAILER_5_WHEEL_NAME = 'Trailer (5th Wheel)';
    public const VEHICLE_TYPE_TRUCK_DAYCAB = 16;
    public const VEHICLE_TYPE_TRUCK_DAYCAB_NAME = 'Truck (daycab)';
    public const VEHICLE_TYPE_TRUCK_SLEEPER = 17;
    public const VEHICLE_TYPE_TRUCK_SLEEPER_NAME = 'Truck (with sleeper)';
    public const VEHICLE_TYPE_VAN = 18;
    public const VEHICLE_TYPE_VAN_NAME = 'Van';
    public const VEHICLE_TYPE_OTHER = 19;
    public const VEHICLE_TYPE_OTHER_NAME = 'Other';

    public const VEHICLE_TYPES = [
        self::VEHICLE_TYPE_ATV => self::VEHICLE_TYPE_ATV_NAME,
        self::VEHICLE_TYPE_BOAT => self::VEHICLE_TYPE_BOAT_NAME,
        self::VEHICLE_TYPE_COUPE_2 => self::VEHICLE_TYPE_COUPE_2_NAME,
        self::VEHICLE_TYPE_FREIGHT => self::VEHICLE_TYPE_FREIGHT_NAME,
        self::VEHICLE_TYPE_HEAVY_MACHINERY => self::VEHICLE_TYPE_HEAVY_MACHINERY_NAME,
        self::VEHICLE_TYPE_LIVESTOCK => self::VEHICLE_TYPE_LIVESTOCK_NAME,
        self::VEHICLE_TYPE_MOTORCYCLE => self::VEHICLE_TYPE_MOTORCYCLE_NAME,
        self::VEHICLE_TYPE_PICKUP_4 => self::VEHICLE_TYPE_PICKUP_4_NAME,
        self::VEHICLE_TYPE_PICKUP_2 => self::VEHICLE_TYPE_PICKUP_2_NAME,
        self::VEHICLE_TYPE_RV => self::VEHICLE_TYPE_RV_NAME,
        self::VEHICLE_TYPE_SEDAN => self::VEHICLE_TYPE_SEDAN_NAME,
        self::VEHICLE_TYPE_SUV => self::VEHICLE_TYPE_SUV_NAME,
        self::VEHICLE_TYPE_TRAILER_BUMPER => self::VEHICLE_TYPE_TRAILER_BUMPER_NAME,
        self::VEHICLE_TYPE_TRAILER_GOOSENECK => self::VEHICLE_TYPE_TRAILER_GOOSENECK_NAME,
        self::VEHICLE_TYPE_TRAILER_5_WHEEL => self::VEHICLE_TYPE_TRAILER_5_WHEEL_NAME,
        self::VEHICLE_TYPE_TRUCK_DAYCAB => self::VEHICLE_TYPE_TRUCK_DAYCAB_NAME,
        self::VEHICLE_TYPE_TRUCK_SLEEPER => self::VEHICLE_TYPE_TRUCK_SLEEPER_NAME,
        self::VEHICLE_TYPE_VAN => self::VEHICLE_TYPE_VAN_NAME,
        self::VEHICLE_TYPE_OTHER => self::VEHICLE_TYPE_OTHER_NAME,
    ];

    public static function name($value): string
    {
        return self::VEHICLE_TYPES[$value];
    }

    public static function getTypesList(): array
    {
        $data = [];
        foreach (self::VEHICLE_TYPES as $k => $v) {
            $data[] = [
                'id' => $k,
                'name' => $v,
            ];
        }

        return $data;
    }

    public static function ruleIn(): string
    {
        return Rule::in(array_keys(self::VEHICLE_TYPES));
    }
}

