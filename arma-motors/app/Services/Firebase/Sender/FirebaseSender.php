<?php

namespace App\Services\Firebase\Sender;

use App\Models\Notification\Fcm;
use App\Models\User\User;
use App\Services\Firebase\FcmAction;

interface FirebaseSender
{
    public function send(User $user, FcmAction $action, null|Fcm $fcm = null);
}
