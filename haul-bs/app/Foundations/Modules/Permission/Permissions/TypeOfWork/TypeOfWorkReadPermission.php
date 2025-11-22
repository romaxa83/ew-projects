<?php

namespace App\Foundations\Modules\Permission\Permissions\TypeOfWork;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class TypeOfWorkReadPermission extends BasePermission
{
    public const KEY = TypeOfWorkPermissionsGroup::KEY . '.read';
}
