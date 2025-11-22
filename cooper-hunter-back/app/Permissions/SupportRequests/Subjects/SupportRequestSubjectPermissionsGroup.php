<?php

namespace App\Permissions\SupportRequests\Subjects;

use Core\Permissions\BasePermissionGroup;

class SupportRequestSubjectPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'support_request.subject';

    public function getName(): string
    {
        return __('permissions.support_request.subject.group');
    }

    public function getPosition(): int
    {
        return 90;
    }
}
