<?php

namespace App\Permissions\SupportRequests\Subjects;

use Core\Permissions\BasePermission;

class SupportRequestSubjectDeletePermission extends BasePermission
{

    public const KEY = 'support_request.subject.delete';

    public function getName(): string
    {
        return __('permissions.support_request.subject.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
