<?php

namespace App\Permissions\Commercial\CommercialProjects;

use Core\Permissions\BasePermission;

class CommercialStartCommissioningPermission extends BasePermission
{
    public const KEY = CommercialProjectPermissionGroup::KEY . '.start-commissioning';

    public function getName(): string
    {
        return __('permissions.commercial_project.start_commissioning');
    }

    public function getPosition(): int
    {
        return 5;
    }
}
