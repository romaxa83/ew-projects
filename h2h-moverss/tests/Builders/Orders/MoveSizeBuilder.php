<?php

namespace Tests\Builders\Orders;

use App\Models\MoveSize;
use Tests\Builders\BaseBuilder;

// todo deprecated удалить после удаления MoveSize
class MoveSizeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return MoveSize::class;
    }
}
