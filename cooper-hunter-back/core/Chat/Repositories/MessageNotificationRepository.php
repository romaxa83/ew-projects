<?php

namespace Core\Chat\Repositories;

use Core\Chat\Models\MessageNotification;
use Illuminate\Database\Eloquent\Builder;

class MessageNotificationRepository
{
    public function __construct(protected MessageNotification $model)
    {
    }

    protected function query(): MessageNotification|Builder
    {
        return $this->model->newQuery();
    }
}
