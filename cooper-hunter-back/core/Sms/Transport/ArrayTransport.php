<?php

namespace Core\Sms\Transport;

use Core\Sms\SmsMessage;

class ArrayTransport extends Transport
{
    public function send(SmsMessage $message): void
    {
    }

    public function queue(SmsMessage $message): void
    {
    }
}
