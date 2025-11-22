<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupQuestionAnswer extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'question-answer';
    }

    public function getPermissions(): array
    {
        return [
            'create',
            'read',
            'update',
            'delete',
        ];
    }
}
