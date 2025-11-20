<?php

namespace App\IPTelephony\Events\Subscriber;

use App\Models\Employees\Employee;

class SubscriberUpdateOrCreateEvent
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

