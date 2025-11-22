<?php

namespace App\Dto\Permissions;

class RoleDto
{
    private string $name;

    private array $permissions;

    private function __construct()
    {
    }

    public static function fromArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->permissions = $args['permissions'];

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
