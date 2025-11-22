<?php

namespace App\Foundations\Modules\Permission\Dto;


final readonly class RoleDto
{
    public string $name;
    public string $guard;
    public array $permissionsIds;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->guard = $args['guard'];

        $self->permissionsIds = $args['permission_ids'] ?? [];

        return $self;
    }
}

