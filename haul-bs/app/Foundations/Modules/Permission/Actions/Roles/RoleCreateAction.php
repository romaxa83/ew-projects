<?php

namespace App\Foundations\Modules\Permission\Actions\Roles;

use App\Foundations\Modules\Permission\Dto\RoleDto;
use App\Foundations\Modules\Permission\Models\Role;

final readonly class RoleCreateAction
{
    public function exec(RoleDto $dto): Role
    {
        return make_transaction(function() use ($dto) {
            $model = new Role();
            $model->name = $dto->name;
            $model->guard_name = $dto->guard;

            $model->save();

            $model->permissions()->sync($dto->permissionsIds);

            return $model;
        });
    }
}




