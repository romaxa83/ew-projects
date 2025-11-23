<?php

namespace App\Enums\Catalog;

use App\Enums\Base\ForSelect;
use App\Enums\Base\InvokableCases;
use App\Enums\Base\Label;
use App\Enums\Base\RuleIn;

/**
 * @method static Studio()
 * @method static Bedroom_1()
 * @method static Bedroom_2()
 * @method static Bedroom_3()
 * @method static Bedroom_4()
 * @method static Storage()
 */

enum MoveSizeTypeEnum: string
{
    use InvokableCases;
    use Label;
    use ForSelect;
    use RuleIn;

    case Studio = "1";
    case Bedroom_1  = "2";
    case Bedroom_2 = "3";
    case Bedroom_3  = "4";
    case Bedroom_4  = "5";
    case Storage  = "6";

    public function label(): string
    {
        return match ($this->value){
            static::Studio->value => 'Studio',
            static::Bedroom_1->value => '1 Bedroom',
            static::Bedroom_2->value => '2 Bedroom',
            static::Bedroom_3->value => '3 Bedroom',
            static::Bedroom_4->value => '4 Bedroom +',
            static::Storage->value => 'Storage',
        };
    }

    public function labelAsName(): string
    {
        return $this->label();
    }
}
