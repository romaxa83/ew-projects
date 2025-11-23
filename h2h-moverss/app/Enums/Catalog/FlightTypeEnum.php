<?php

namespace App\Enums\Catalog;

use App\Enums\Base\ForSelect;
use App\Enums\Base\InvokableCases;
use App\Enums\Base\Label;

/**
 * @method static Flight_05()
 * @method static Flight_1()
 * @method static Flight_15()
 * @method static Flight_2()
 * @method static Flight_3()
 * @method static Flight_4()
 * @method static Flight_5()
 */

enum FlightTypeEnum: string
{
    use InvokableCases;
    use Label;
    use ForSelect;

    case Flight_05 = "6";
    case Flight_1  = "1";
    case Flight_15 = "7";
    case Flight_2  = "2";
    case Flight_3  = "3";
    case Flight_4  = "4";
    case Flight_5  = "5";

    public function label(): string
    {
        return match ($this->value){
            static::Flight_05->value => '0.5 flight',
            static::Flight_1->value => '1 flight',
            static::Flight_15->value => '1.5 flight',
            static::Flight_2->value => '2 flights',
            static::Flight_3->value => '3 flights',
            static::Flight_4->value => '4 flights',
            static::Flight_5->value => '5+ flights',
        };
    }

    public function labelAsName(): string
    {
        return $this->label();
    }

    public function additionalForSelect(): array
    {
        return [
            'sort' => [
                static::Flight_05->value => '1',
                static::Flight_1->value => '2',
                static::Flight_15->value => '3',
                static::Flight_2->value => '4',
                static::Flight_3->value => '5',
                static::Flight_4->value => '6',
                static::Flight_5->value => '7',
            ]
        ];
    }
}
