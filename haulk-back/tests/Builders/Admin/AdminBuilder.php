<?php

namespace Tests\Builders\Admin;

use App\Models\Admins\Admin;
use Tests\Builders\BaseBuilder;

class AdminBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Admin::class;
    }
}
