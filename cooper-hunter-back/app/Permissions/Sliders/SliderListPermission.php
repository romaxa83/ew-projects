<?php

namespace App\Permissions\Sliders;

use Core\Permissions\BasePermission;

class SliderListPermission extends BasePermission
{
    public const KEY = SliderPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.slider.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
