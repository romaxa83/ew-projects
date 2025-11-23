<?php

namespace WezomCms\SmsVerify\Services\Sms;

interface SmsSender
{
    public function send(string $number, string  $text): void;
}
