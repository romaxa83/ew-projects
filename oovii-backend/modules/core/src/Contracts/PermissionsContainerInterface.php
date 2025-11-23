<?php

namespace WezomCms\Core\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface PermissionsContainerInterface
{
    /**
     * @param  string  $key
     * @param  string|null  $name
     * @param  array  $gates
     * @return PermissionsContainerInterface
     */
    public function add(
        string $key,
        ?string $name,
        array $gates = ['view', 'create', 'edit', 'delete']
    ): PermissionsContainerInterface;

    /**
     * Add one ability.
     *
     * @param  string  $ability
     * @param  string|null  $name
     * @param  callable|null  $callback
     * @return PermissionsContainerInterface
     */
    public function addItem(string $ability, ?string $name, callable $callback = null): PermissionsContainerInterface;

    /**
     * @param  string  $ability
     * @param  string|null  $name
     * @return PermissionsContainerInterface
     */
    public function editSettings(string $ability, ?string $name): PermissionsContainerInterface;

    /**
     * Add gate "show" to the last permission.
     *
     * @return PermissionsContainerInterface
     */
    public function withShow(): PermissionsContainerInterface;

    /**
     * Add gate "edit-settings" to the last permission.
     *
     * @return PermissionsContainerInterface
     */
    public function withEditSettings(): PermissionsContainerInterface;

    /**
     * Add gate "restore", "force-delete" to the last permission.
     *
     * @return PermissionsContainerInterface
     */
    public function withSoftDeletes(): PermissionsContainerInterface;

    /**
     * @return iterable|Collection
     */
    public function getAll(): iterable;

    /**
     * Return Model ability name for check user access.
     *
     * @param  Model  $model
     * @param  string|null  $action
     * @return string
     */
    public function getModelAbility(Model $model, string $action = null): string;
}
