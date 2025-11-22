<?php

namespace App\Permissions\SupportRequests\Subjects;

use Core\Permissions\BasePermission;

class SupportRequestSubjectCreatePermission extends BasePermission
{

    public const KEY = 'support_request.subject.create';

    public function getName(): string
    {
        return __('permissions.support_request.subject.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
