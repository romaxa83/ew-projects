<?php

namespace App\Dto\Departments;

final class DepartmentDto
{
    public string $name;
    public bool $active;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = data_get($args, 'name');
        $self->active = data_get($args, 'active', true);

        return $self;
    }
}
