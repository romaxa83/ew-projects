<?php

namespace Tests\Traits\Builders;

trait EmailVerifyBuilder
{
    public function emailVerifyBuilder()
    {
        return new \Tests\_Helpers\EmailVerifyBuilder();
    }
}
