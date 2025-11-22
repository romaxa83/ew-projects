<?php

namespace App\Permissions\Sliders;

use Core\Permissions\BasePermission;

class SliderUpdatePermission extends BasePermission
{
    public const KEY = SliderPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.slider.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
