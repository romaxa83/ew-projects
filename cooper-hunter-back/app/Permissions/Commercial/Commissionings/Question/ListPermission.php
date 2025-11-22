<?php

namespace App\Permissions\Commercial\Commissionings\Question;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.commissioning.question.grants.list');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
