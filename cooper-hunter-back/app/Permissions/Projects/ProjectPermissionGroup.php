<?php

namespace App\Permissions\Projects;

use Core\Permissions\BasePermissionGroup;

class ProjectPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'project';

    public function getName(): string
    {
        return __('permissions.project.group');
    }

    public function getPosition(): int
    {
        return 1000;
    }
}
