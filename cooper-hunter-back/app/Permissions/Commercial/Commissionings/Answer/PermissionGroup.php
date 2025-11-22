<?php

namespace App\Permissions\Commercial\Commissionings\Answer;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'commissioning_answer';

    public function getName(): string
    {
        return __('permissions.commissioning.answer.group');
    }
}

