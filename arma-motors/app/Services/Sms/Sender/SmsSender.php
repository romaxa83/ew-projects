<?php

namespace App\Services\Sms\Sender;

use App\ValueObjects\Phone;

interface SmsSender
{
    public function send(Phone $number, string  $text): void;
}
