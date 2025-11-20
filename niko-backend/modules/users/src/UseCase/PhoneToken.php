<?php

namespace WezomCms\Users\UseCase;

use Carbon\Carbon;

class PhoneToken
{
    private $lengthToken;
    private $expireToken;

    public function __construct()
    {
        $this->lengthToken = config('cms.users.users.sms_token_length', 4);
        $this->expireToken = config('cms.users.users.sms_token_expire', 60);
    }

    /**
     * @throws \Exception
     */
    public function token(): string
    {
        return (string)random_int($this->getMin(), $this->getMax());
    }

    /**
     * @return Carbon
     */
    public function expired()
    {
        return Carbon::now()->addSecond($this->expireToken);
    }

    private function getMin(): int
    {
        $min = '1';
        for ($i = 0; $i < $this->lengthToken - 1; $i++){
            $min .= '0';
        }

        return (int)$min;
    }

    private function getMax(): int
    {
        $max = '9';
        for ($i = 0; $i < $this->lengthToken - 1; $i++){
            $max .= '9';
        }

        return (int)$max;
    }
}
