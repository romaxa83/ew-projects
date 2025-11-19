<?php

namespace Wezom\Core\Permissions;

use Illuminate\Support\Collection;
use Wezom\Core\Contracts\Permissions\PermissionGroup as PermissionGroupInterface;
use Wezom\Core\Enums\PermissionActionEnum;

class PermissionGroup implements PermissionGroupInterface
{
    protected Collection $permissions;

    public function __construct(
        protected string $group,
        protected string $name,
        array $actions,
        protected int $sort = 0
    ) {
        $i = 0;
        $this->permissions = collect();
        foreach ($actions as $index => $action) {
            $actionName = $action instanceof PermissionActionEnum ? $action->value : $action;

            $permission = is_numeric($index)
                ? new Permission($group, $action, "core::permissions.gates.$actionName", $i)
                : new Permission($group, $index, $actionName, $i);

            $this->permissions->push($permission);
            $i++;
        }
    }

    /**
     * {@inheritDoc}
     */
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
        return $this->group;
    }

    public function getName(): string
    {
        return __($this->name);
    }

    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getPosition(): int
    {
        return $this->sort;
    }

    public function addPermission(Permission $permission): static
    {
        $this->permissions->push($permission);

        return $this;
    }
}
