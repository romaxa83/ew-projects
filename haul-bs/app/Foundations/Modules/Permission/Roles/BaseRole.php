<?php

namespace App\Foundations\Modules\Permission\Roles;

abstract readonly class BaseRole  implements DefaultRole
{
    public function getName(): string
    {
        return static::NAME;
    }

    public function getGuard(): string
    {
        return static::GUARD;
    }
}





