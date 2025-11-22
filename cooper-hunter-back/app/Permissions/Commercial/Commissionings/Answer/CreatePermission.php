<?php

namespace App\Permissions\Commercial\Commissionings\Answer;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.commissioning.answer.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
