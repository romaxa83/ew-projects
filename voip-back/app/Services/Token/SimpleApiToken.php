<?php

namespace App\Services\Token;

class SimpleApiToken implements ApiToken
{
    private $config;

    public function __construct()
    {
        $this->config = config('api');
    }

    public function getToken(): string
    {
        return $this->config['access_token'];
    }

    public function checkToken(string $token): bool
    {
        return $this->config['access_token'] === $token;
    }
}

