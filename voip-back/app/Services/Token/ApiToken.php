<?php

namespace App\Services\Token;

interface ApiToken
{
    public function getToken(): string;

    public function checkToken(string $token): bool;
}
