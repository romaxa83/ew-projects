<?php

namespace App\IPTelephony\Events\QueueMember;

use App\Models\Employees\Employee;

class QueueMemberUpdateEvent
{
    public function __construct(
        protected Employee $model
    )
    {}

    public function getModel(): Employee
    {
        return $this->model;
    }
}
