<?php

namespace Tests\Builders\Admins;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use Tests\Builders\BaseBuilder;

class AdminBuilder extends BaseBuilder
{
    protected null|Role $role = null;

    function modelClass(): string
    {
        return Admin::class;
    }

    public function setRole(Role $model): self
    {
        $this->role = $model;
        return $this;
    }

    protected function afterSave($model): void
    {
        /** @var $model Admin */
        if($this->role){
            $model->assignRole($this->role);
            return;
        }
    }

    protected function afterClear(): void
    {
        $this->role = null;
    }
}
