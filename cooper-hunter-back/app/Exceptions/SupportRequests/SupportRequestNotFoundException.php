<?php

namespace App\Exceptions\SupportRequests;

use Core\Exceptions\TranslatedException;

class SupportRequestNotFoundException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.support_request.not_found'));
    }
}
