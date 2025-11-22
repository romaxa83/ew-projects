<?php

namespace App\Permissions\Projects;

use Core\Permissions\BasePermission;

class ProjectCreatePermission extends BasePermission
{
    public const KEY = ProjectPermissionGroup::KEY.'.create';

    public function getName(): string
    {
        return __('permissions.project.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
