<?php

namespace Core\Sms\Transport;

use Core\Sms\SmsMessage;
use Illuminate\Contracts\Queue\Factory as QueueContract;

abstract class Transport
{
    protected QueueContract $queue;

    abstract public function send(SmsMessage $message): void;

    abstract public function queue(SmsMessage $message): void;

    public function setQueue(QueueContract $queue): void
    {
        $this->queue = $queue;
    }

    public function unsetQueue(): void
    {
        unset($this->queue);
    }
}
