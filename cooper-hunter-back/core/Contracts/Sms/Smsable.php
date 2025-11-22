<?php

namespace Core\Contracts\Sms;

interface Smsable
{
    public function body(): string;
}
