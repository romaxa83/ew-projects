<?php

namespace App\Exceptions\SupportRequests;

use Core\Exceptions\TranslatedException;

class SubjectUsedInRequestsException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.support_request.subject_used_in_requests'));
    }
}
