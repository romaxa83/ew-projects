<?php

namespace App\Permissions\Commercial\CommercialProjects;

use Core\Permissions\BasePermission;

class CommercialProjectCreatePermission extends BasePermission
{
    public const KEY = CommercialProjectPermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.commercial_project.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}