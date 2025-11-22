<?php

namespace App\Permissions\SupportRequests\Subjects;

use Core\Permissions\BasePermission;

class SupportRequestSubjectUpdatePermission extends BasePermission
{

    public const KEY = 'support_request.subject.update';

    public function getName(): string
    {
        return __('permissions.support_request.subject.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
