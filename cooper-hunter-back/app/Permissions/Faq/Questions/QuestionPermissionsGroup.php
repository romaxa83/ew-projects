<?php

namespace App\Permissions\Faq\Questions;

use Core\Permissions\BasePermissionGroup;

class QuestionPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'question';

    public function getName(): string
    {
        return __('permissions.question.group');
    }

    public function getPosition(): int
    {
        return 66;
    }
}
