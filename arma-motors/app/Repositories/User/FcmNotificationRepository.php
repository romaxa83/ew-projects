<?php

namespace App\Repositories\User;

use App\Models\Notification\Fcm;
use App\Repositories\AbstractRepository;

class FcmNotificationRepository extends AbstractRepository
{
    public function query()
    {
        return Fcm::query();
    }
}
