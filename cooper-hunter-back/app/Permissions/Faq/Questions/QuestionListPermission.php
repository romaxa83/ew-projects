<?php

namespace App\Permissions\Faq\Questions;

use Core\Permissions\BasePermission;

class QuestionListPermission extends BasePermission
{
    public const KEY = 'question.list';

    public function getName(): string
    {
        return __('permissions.question.grants.list');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
