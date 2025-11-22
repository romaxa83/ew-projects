<?php

namespace Core\Sms;

use Core\Contracts\Sms\SmsTransportContract;

interface SmsFactory
{
    public function smsTransport(?string $name = null): SmsTransportContract;
}
