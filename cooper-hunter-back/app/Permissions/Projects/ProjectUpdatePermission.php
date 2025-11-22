<?php

namespace App\Permissions\Projects;

use Core\Permissions\BasePermission;

class ProjectUpdatePermission extends BasePermission
{
    public const KEY = ProjectPermissionGroup::KEY.'.update';

    public function getName(): string
    {
        return __('permissions.project.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
