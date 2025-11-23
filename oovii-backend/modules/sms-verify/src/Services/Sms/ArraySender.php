<?php

namespace WezomCms\SmsVerify\Services\Sms;

class ArraySender implements SmsSender
{
    private $messages = [];

    public function send(string $number, string $text): void
    {
        $this->messages[] = [
            'to' => '+' . trim($number, '+'),
            'text' => $text
        ];
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
