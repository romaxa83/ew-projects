<?php

namespace App\Services\FcmNotification\Receivers;

use App\Services\FcmNotification\FcmNotyPayload;
use App\Services\FcmNotification\TemplateManager;

interface ReceiverStrategy
{
    public function getReceives(TemplateManager $templateManager): FcmNotyPayload;
}
