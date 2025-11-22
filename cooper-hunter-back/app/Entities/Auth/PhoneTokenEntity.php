<?php

namespace App\Entities\Auth;

class PhoneTokenEntity
{
    public function __construct(
        public string $token,
        public int $expires_at,
    ) {
    }
}
