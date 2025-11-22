<?php

namespace App\Permissions\Faq\Questions;

use Core\Permissions\BasePermission;

class QuestionAnswerPermission extends BasePermission
{
    public const KEY = 'question.answer';

    public function getName(): string
    {
        return __('permissions.question.grants.answer');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
