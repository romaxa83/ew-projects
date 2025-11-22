<?php

namespace App\Permissions\Commercial\Commissionings\Question;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.commissioning.question.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}

