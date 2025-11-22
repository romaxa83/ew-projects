<?php

namespace App\Traits\Auth;

use Exception;

trait CodeGenerator
{
    /** @throws Exception */
    protected function generateVerificationCode(): string
    {
        return random_int(100000, 999999);
    }

    /** @throws Exception */
    protected function generateSmsCode(): string
    {
        if (config('sms.default') === 'array') {
            return '0000';
        }

        return random_int(1000, 9999);
    }
}
