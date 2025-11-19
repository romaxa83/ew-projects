<?php

namespace Wezom\Core\Permissions;

use Wezom\Core\Contracts\Permissions\PermissionsContainer;
use Wezom\Core\Enums\PermissionActionEnum;

class NullDriver implements PermissionsContainer
{
    public function add(
        string $key,
        ?string $name,
        array $gates = [
            PermissionActionEnum::CREATE,
            PermissionActionEnum::VIEW,
            PermissionActionEnum::UPDATE,
            PermissionActionEnum::DELETE,
        ],
        int $sort = 0
    ): PermissionsContainer {
        return $this;
    }

    public function editSettings(string $key, string $name, int $sort = 0): static
    {
        return $this;
    }

    public function withEditSettings(): static
    {
        return $this;
    }

    public function getAll(): iterable
    {
        return collect();
    }

    public function has(string $key): bool
    {
        return true;
    }

    public function checkExists(string $key): void
    {
    }
}
