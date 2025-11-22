<?php

namespace Core\Contracts\Sms;

use Illuminate\Database\Eloquent\Model;

interface SmsTransportContract
{
    public function to(string|Model $phone): self;

    public function send(Smsable $smsable): void;
}
