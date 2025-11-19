<?php

namespace Wezom\Core\Contracts\Permissions;

use Illuminate\Support\Collection;
use Wezom\Core\Enums\PermissionActionEnum;
use Wezom\Core\Exceptions\Auth\PermissionNotRegisteredException;

interface PermissionsContainer
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
    ): PermissionsContainer;

    public function editSettings(string $key, string $name, int $sort = 0): PermissionsContainer;

    public function withEditSettings(): PermissionsContainer;

    /**
     * @return Collection<PermissionGroup[]>
     */
    public function getAll(): iterable;

    public function has(string $key): bool;

    /**
     * @throws PermissionNotRegisteredException
     */
    public function checkExists(string $key): void;
}
