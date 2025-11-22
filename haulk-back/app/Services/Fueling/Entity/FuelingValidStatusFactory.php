<?php

namespace App\Services\Fueling\Entity;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Models\Fueling\Fueling;

class FuelingValidStatusFactory
{
    public static function create(Fueling $fueling): AbstractFuelingValidStatus
    {
        switch ($fueling->provider) {
            case FuelCardProviderEnum::QUIKQ:
                return new FuelingValidStatusQuikq($fueling);
            default:
                return new FuelingValidStatusEfs($fueling);
        }
    }
}
