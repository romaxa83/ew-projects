<?php

namespace Tests\Builders\Orders;

use App\Models\BuildingType;
use Tests\Builders\BaseBuilder;

// todo удалить после удаления BuildingType
class BuildingTypeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return BuildingType::class;
    }
}
