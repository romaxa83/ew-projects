<?php

namespace App\Permissions\Commercial\CommercialProjects;

use Core\Permissions\BasePermission;

class CommercialProjectDeletePermission extends BasePermission
{
    public const KEY = CommercialProjectPermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.commercial_project.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}