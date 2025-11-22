<?php

namespace App\Permissions\SupportRequests\Subjects;

use Core\Permissions\BasePermission;

class SupportRequestSubjectListPermission extends BasePermission
{

    public const KEY = 'support_request.subject.list';

    public function getName(): string
    {
        return __('permissions.support_request.subject.grants.list');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
