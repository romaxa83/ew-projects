<?php

namespace Wezom\Core\Testing\Crud;

use Illuminate\Database\Eloquent\Model;
use LogicException;
use Wezom\Core\Testing\TestCase;

abstract class CrudTestAbstract extends TestCase
{
    /**
     * @return class-string
     */
    abstract protected function model(): string;

    protected function callLoginAsAdmin(): mixed
    {
        if (method_exists($this, 'loginAsAdmin')) {
            return $this->loginAsAdmin();
        }

        throw new LogicException('Missing use trait Wezom\Admins\Traits\AdminTestTrait');
    }

    protected function callLoginAsAdminWithPermissions(
        array $permissions = [],
        mixed $admin = null,
        ?string $roleName = null
    ): mixed {
        if (method_exists($this, 'loginAsAdminWithPermissions')) {
            return $this->loginAsAdminWithPermissions($permissions, $admin, $roleName);
        }

        throw new LogicException('Missing use trait Wezom\Admins\Traits\AdminTestTrait');
    }

    protected function basePermissionName(): string
    {
        $modelName = $this->model();

        $model = new $modelName();
        assert($model instanceof Model);

        return snake_case(camel_case($model->getTable()), '-');
    }
}
