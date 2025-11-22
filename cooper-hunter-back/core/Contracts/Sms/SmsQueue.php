<?php

namespace Core\Contracts\Sms;

use Illuminate\Contracts\Queue\Factory as QueueContract;

interface SmsQueue
{
    public function setQueue(QueueContract $queue): SmsTransportContract;
}
