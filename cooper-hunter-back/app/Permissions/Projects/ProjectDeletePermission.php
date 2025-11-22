<?php

namespace App\Permissions\Projects;

use Core\Permissions\BasePermission;

class ProjectDeletePermission extends BasePermission
{
    public const KEY = ProjectPermissionGroup::KEY.'.delete';

    public function getName(): string
    {
        return __('permissions.project.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
