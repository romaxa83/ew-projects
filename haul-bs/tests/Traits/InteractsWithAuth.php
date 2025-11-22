<?php

namespace Tests\Traits;

use App\Foundations\Modules\Permission\Models\Role;
use App\Models\Users\User;
use Tests\Builders\Users\UserBuilder;

trait InteractsWithAuth
{
    protected function loginUserAsSuperAdmin(User $model = null): User
    {
        if (!$model) {
            if(property_exists($this, 'userBuilder')){
                $model = $this->userBuilder->create();
            } else {
                $builder = resolve(UserBuilder::class);
                $model = $builder->create();
            }
        }

        $role = Role::superAdmin()->first();

        $model->assignRole($role);

        $this->actingAs($model, $role->guard_name);

        return $model;
    }

    protected function loginUserAsMechanic(User $model = null): User
    {
        if (!$model) {
            if(property_exists($this, 'userBuilder')){
                $model = $this->userBuilder->create();
            } else {
                $builder = resolve(UserBuilder::class);
                $model = $builder->create();
            }
        }

        $role = Role::mechanic()->first();

        $model->assignRole($role);

        $this->actingAs($model, $role->guard_name);

        return $model;
    }

    protected function loginUserAsAdmin(User $model = null): User
    {
        if (!$model) {
            if(property_exists($this, 'userBuilder')){
                $model = $this->userBuilder->create();
            } else {
                $builder = resolve(UserBuilder::class);
                $model = $builder->create();
            }
        }

        $role = Role::admin()->first();

        $model->assignRole($role);

        $this->actingAs($model, $role->guard_name);

        return $model;
    }

    protected function loginUserAsSalesManager(User $model = null): User
    {
        if (!$model) {
            $model = $this->getUser();
        }

        $role = Role::salesManager()->first();

        $model->assignRole($role);

        $this->actingAs($model, $role->guard_name);

        return $model;
    }

    private function getUser(): User
    {
        if(property_exists($this, 'userBuilder')){
            $model = $this->userBuilder->create();
        } else {
            $builder = resolve(UserBuilder::class);
            $model = $builder->create();
        }

        return $model;
    }
}


