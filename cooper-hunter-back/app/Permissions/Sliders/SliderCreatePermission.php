<?php

namespace App\Permissions\Sliders;

use Core\Permissions\BasePermission;

class SliderCreatePermission extends BasePermission
{
    public const KEY = SliderPermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.slider.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
