<?php

namespace Wezom\Core\Dto;

use Wezom\Core\Enums\RoleEnum;

class RoleDto
{
    private array $permissions;
    private string $name;
    private ?string $note;
    private null|string|RoleEnum $system_type;

    public static function makeByArgs(array $args): RoleDto
    {
        $self = new RoleDto();

        $self->name = $args['name'];
        $self->note = $args['note'] ?? null;
        $self->system_type = $args['system_type'] ?? null;
        $self->permissions = $args['permissions'] ?? [];

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getSystemType(): null|string|RoleEnum
    {
        return $this->system_type;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
