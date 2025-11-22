<?php

namespace App\Contracts\Members;

interface MemberMustVerifyEmail
{
    public function isEmailVerified(): bool;
}
