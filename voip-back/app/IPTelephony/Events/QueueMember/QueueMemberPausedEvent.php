<?php

namespace App\IPTelephony\Events\QueueMember;

use App\Models\Employees\Employee;

class QueueMemberPausedEvent
{
    public function __construct(
        protected Employee $model,
        protected bool $paused = true
    )
    {}

    public function getModel(): Employee
    {
        return $this->model;
    }

    public function paused(): bool
    {
        return $this->paused;
    }
}

