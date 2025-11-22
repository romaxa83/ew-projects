<?php

namespace App\Permissions\Commercial\CommercialProjects;

use Core\Permissions\BasePermission;

class CommercialProjectUpdatePermission extends BasePermission
{
    public const KEY = CommercialProjectPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.commercial_project.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}