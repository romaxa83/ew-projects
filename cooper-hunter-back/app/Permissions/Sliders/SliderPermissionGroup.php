<?php

namespace App\Permissions\Sliders;

use Core\Permissions\BasePermissionGroup;

class SliderPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'slider';

    public function getName(): string
    {
        return __('permissions.slider.group');
    }

    public function getPosition(): int
    {
        return 1000;
    }
}
