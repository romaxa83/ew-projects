<?php

namespace App\Permissions\Commercial\CommercialProjects;

use Core\Permissions\BasePermission;

class CommercialProjectListPermission extends BasePermission
{
    public const KEY = CommercialProjectPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.commercial_project.grants.list');
    }

    public function getPosition(): int
    {
        return 0;
    }
}