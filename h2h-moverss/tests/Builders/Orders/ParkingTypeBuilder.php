<?php

namespace Tests\Builders\Orders;

use App\Models\ParkingType;
use Tests\Builders\BaseBuilder;

// todo удалить после удаления ParkingType
class ParkingTypeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return ParkingType::class;
    }
}
