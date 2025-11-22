<?php

namespace App\Foundations\Modules\Permission\Actions\Roles;

use App\Foundations\Modules\Permission\Dto\RoleDto;
use App\Foundations\Modules\Permission\Models\Role;

final readonly class RoleUpdateAction
{
    public function exec(Role $model,  RoleDto $dto): Role
    {
        return make_transaction(function() use ($model, $dto) {
            $model->name = $dto->name;

            $model->save();

            $model->permissions()->sync($dto->permissionsIds);

            return $model;
        });
    }
}
