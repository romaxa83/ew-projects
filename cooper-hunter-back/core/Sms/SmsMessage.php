<?php

namespace Core\Sms;

use Core\Contracts\Sms\Smsable;

class SmsMessage
{
    protected string $to;
    protected string $body;

    public function __construct(protected Smsable $sms)
    {
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    public function getBody(): string
    {
        return $this->sms->body();
    }

    public function getSmsable(): Smsable
    {
        return $this->sms;
    }
}
