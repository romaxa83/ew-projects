<?php

namespace App\Enums\Catalog;

use App\Enums\Base\ForSelect;
use App\Enums\Base\InvokableCases;
use App\Enums\Base\Label;

/**
 * @method static Moving()
 * @method static Packing()
 * @method static Loading()
 * @method static Unloading()
 * @method static Rearrangement()
 * @method static Junk()
 * @method static Unpacking()
 * @method static In_Home_Estimate()
 */

enum WorkTypeEnum: string
{
    use InvokableCases;
    use Label;
    use ForSelect;

    case Moving           = "1";
    case Packing          = "2";
    case Loading          = "3";
    case Unloading        = "4";
    case Rearrangement    = "5";
    case Junk             = "6";
    case Unpacking        = "8";
    case In_Home_Estimate = "9";

    public function label(): string
    {
        return match ($this->value){
            static::Moving->value => 'Moving',
            static::Packing->value => 'Packing',
            static::Loading->value => 'Loading',
            static::Unloading->value => 'Unloading',
            static::Rearrangement->value => 'Rearrangement',
            static::Junk->value => 'Junk',
            static::Unpacking->value => 'Unpacking',
            static::In_Home_Estimate->value => 'In-Home Estimate',
        };
    }

    public function labelAsName(): string
    {
        return $this->label();
    }
}
