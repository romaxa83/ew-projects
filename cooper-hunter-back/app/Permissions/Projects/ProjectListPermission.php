<?php

namespace App\Permissions\Projects;

use Core\Permissions\BasePermission;

class ProjectListPermission extends BasePermission
{
    public const KEY = ProjectPermissionGroup::KEY.'.list';

    public function getName(): string
    {
        return __('permissions.project.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
