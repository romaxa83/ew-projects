<?php

namespace App\Permissions\Commercial\CommercialProjects;

use Core\Permissions\BasePermissionGroup;

class CommercialProjectPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'commercial_project';

    public function getName(): string
    {
        return __('permissions.commercial_project.group');
    }
}