<?php

namespace App\Foundations\Modules\Permission\Dto;

final readonly class PermissionDto
{
    public string $name;
    public string $guard;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->guard = $args['guard'];

        return $self;
    }
}


