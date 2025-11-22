<?php

namespace App\Foundations\Modules\Permission\Permissions\TypeOfWork;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class TypeOfWorkDeletePermission extends BasePermission
{
    public const KEY = TypeOfWorkPermissionsGroup::KEY . '.delete';
}
