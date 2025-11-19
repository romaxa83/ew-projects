<?php

namespace Wezom\Core\Permissions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;
use Wezom\Admins\AdminConst;
use Wezom\Core\Contracts\Permissions\PermissionsContainer;
use Wezom\Core\Enums\PermissionActionEnum;
use Wezom\Core\Exceptions\Auth\PermissionNotRegisteredException;

class Permissions implements PermissionsContainer
{
    /**
     * @var Collection<string, PermissionGroup>
     */
    protected Collection $items;

    public function __construct(protected string $guard)
    {
        $this->items = collect();
    }

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
    ): static {
        $key = static::transformKey($key);
        $this->items->put($key, new PermissionGroup($key, $name, $gates, $sort));

        return $this;
    }

    public function editSettings(string $key, string $name, int $sort = 0): static
    {
        return $this->add($key, $name, [PermissionActionEnum::EDIT_SETTINGS], $sort);
    }

    public function withEditSettings(): static
    {
        /** @var PermissionGroup|null $group */
        $group = $this->items->last();
        if (!$group) {
            throw new RuntimeException('No permission groups for this guard: ' . $this->guard);
        }

        $group->addPermission(
            new Permission(
                $group->getKey(),
                PermissionActionEnum::EDIT_SETTINGS,
                'core::permissions.gates.edit_settings',
                $group->getPermissions()->count() + 1
            )
        );

        return $this;
    }

    /**
     * @return Collection<string, PermissionGroup>|iterable
     */
    public function getAll(): iterable|Collection
    {
        return $this->items;
    }

    public static function transformKey(string $key): string
    {
        if (!is_a($key, Model::class, true)) {
            return $key;
        }

        $key = (new $key())->getTable();

        return str_replace('_', '-', $key);
    }

    public function has(string $key): bool
    {
        foreach ($this->items as $group) {
            $permission = $group->getPermissions()
                ->firstWhere(fn (Permission $permission) => $permission->getKey() === $key);
            if ($permission !== null) {
                return true;
            }
        }

        return false;
    }

    public function checkExists(string $key): void
    {
        if ($key !== AdminConst::SUPER_ADMIN && !$this->has($key)) {
            throw new PermissionNotRegisteredException("Ability '$key' is not registered in the PermissionsManager");
        }
    }
}
