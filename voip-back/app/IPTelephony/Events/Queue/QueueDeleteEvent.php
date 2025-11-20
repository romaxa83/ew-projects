<?php

namespace App\IPTelephony\Events\Queue;

use App\Models\Departments\Department;

class QueueDeleteEvent
{
    public function __construct(
        protected Department $model
    )
    {}

    public function getModel(): Department
    {
        return $this->model;
    }
}

