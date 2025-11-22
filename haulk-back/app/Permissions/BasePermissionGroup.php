<?php

namespace App\Permissions;

abstract class BasePermissionGroup implements PermissionGroup
{
    protected array $permissions;

    public function __construct(array $permissions = [])
    {
        $this->permissions = $permissions;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'position' => $this->getPosition(),
            'permissions' => collect($this->getPermissions())->toArray(),
        ];
    }

    public function getKey(): string
    {
        return static::KEY;
    }

    public function getPosition(): int
    {
        return 0;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
