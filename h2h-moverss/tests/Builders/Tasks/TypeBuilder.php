<?php

namespace Tests\Builders\Tasks;

use App\Models\Tasks;
use Tests\Builders\BaseBuilder;

class TypeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Tasks\Type::class;
    }
}
