<?php

namespace App\Permissions\Commercial\Commissionings\Question;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.commissioning.question.grants.updtae');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
