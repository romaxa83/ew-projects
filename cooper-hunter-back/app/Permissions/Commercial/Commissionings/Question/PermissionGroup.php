<?php

namespace App\Permissions\Commercial\Commissionings\Question;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'commissioning_question';

    public function getName(): string
    {
        return __('permissions.commissioning.question.group');
    }
}

