<?php

namespace App\Services\Token;

class SimpleApiToken implements ApiToken
{
    private $config;

    public function __construct()
    {
        $this->config = config('aa.access');
    }

    public function getToken(): string
    {
        return base64_encode($this->config['login'].':'.$this->config['password']);
    }

    public function checkToken(string $token): bool
    {
        $data = explode(':', base64_decode($token));

        return (count($data) == 2) && ($data[0] === $this->config['login']) && ($data[1] === $this->config['password']);
    }
}
