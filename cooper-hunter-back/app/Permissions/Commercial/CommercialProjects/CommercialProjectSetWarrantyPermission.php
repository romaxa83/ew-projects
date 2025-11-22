<?php

namespace App\Permissions\Commercial\CommercialProjects;

use Core\Permissions\BasePermission;

class CommercialProjectSetWarrantyPermission extends BasePermission
{
    public const KEY = CommercialProjectPermissionGroup::KEY . '.set-warranty';

    public function getName(): string
    {
        return __('permissions.commercial_project.set_warranty');
    }

    public function getPosition(): int
    {
        return 6;
    }
}
