<?php

namespace App\Permissions\Sliders;

use Core\Permissions\BasePermission;

class SliderDeletePermission extends BasePermission
{
    public const KEY = SliderPermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.slider.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
