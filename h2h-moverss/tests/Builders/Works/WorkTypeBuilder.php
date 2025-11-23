<?php

namespace Tests\Builders\Works;

use App\Models\WorkTypes;
use Tests\Builders\BaseBuilder;

class WorkTypeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return WorkTypes::class;
    }
}
