<?php

namespace Wezom\Core\Permissions;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Manager;
use InvalidArgumentException;
use Wezom\Admins\AdminsServiceProvider;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Contracts\Permissions\PermissionGroup as PermissionGroupInterface;
use Wezom\Core\Contracts\Permissions\PermissionsContainer;
use Wezom\Core\Enums\PermissionActionEnum;

/**
 * @method Permissions|PermissionsContainer add(string $key, ?string $name, array $gates = [PermissionActionEnum::CREATE, PermissionActionEnum::VIEW, PermissionActionEnum::UPDATE, PermissionActionEnum::DELETE], int $sort = 0)
 * @method Permissions|PermissionsContainer editSettings(string $key, string $name, int $sort = 0)
 * @method Permissions|PermissionsContainer withEditSettings()
 * @method Collection<string, PermissionGroupInterface> getAll()
 */
class PermissionsManager extends Manager
{
    /**
     * {@inheritDoc}
     */
    public function getDefaultDriver(): string
    {
        return $this->container->providerIsLoaded(AdminsServiceProvider::class) ? Admin::GUARD : 'null';
    }

    protected function createGraphqlAdminDriver(): PermissionsContainer
    {
        return $this->container->make(PermissionsContainer::class, [Admin::GUARD]);
    }

    /**
     * Stub driver for mockery
     */
    protected function createNullDriver(): NullDriver
    {
        return new NullDriver();
    }

    /**
     * @alias driver()
     */
    public function guard(?string $guard = null): Permissions|PermissionsContainer
    {
        return $this->driver($guard);
    }

    /**
     * Create a new driver instance.
     *
     * @param  string  $driver
     *
     * @throws BindingResolutionException
     */
    protected function createDriver($driver): mixed
    {
        try {
            return parent::createDriver($driver);
        } catch (InvalidArgumentException) {
            return $this->container->make(PermissionsContainer::class, [$driver]);
        }
    }
}
