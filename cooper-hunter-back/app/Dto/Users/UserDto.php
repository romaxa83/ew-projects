<?php

namespace App\Dto\Users;

use App\Traits\Dto\WithUserProps;

class UserDto
{
    use WithUserProps;

    public static function byArgs(array $args): static
    {
        $self = new static();

        $self->setUserProps($args);

        return $self;
    }
}
