<?php

namespace App\Permissions\Commercial\Commissionings\Question;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.commissioning.question.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
