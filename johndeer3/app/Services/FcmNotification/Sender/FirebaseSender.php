<?php

namespace App\Services\FcmNotification\Sender;

use App\Models\Notification\FcmNotification;
use App\Services\FcmNotification\FcmNotyItemPayload;

interface FirebaseSender
{
    public function __construct(string $url, string $serverKey);

    public function send(FcmNotyItemPayload $data, FcmNotification $fcm = null);
}
