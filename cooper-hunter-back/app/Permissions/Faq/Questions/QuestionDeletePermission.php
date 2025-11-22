<?php

namespace App\Permissions\Faq\Questions;

use Core\Permissions\BasePermission;

class QuestionDeletePermission extends BasePermission
{
    public const KEY = 'question.delete';

    public function getName(): string
    {
        return __('permissions.question.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
