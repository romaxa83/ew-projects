<?php

namespace App\Services\SendPulse;

interface SendPulseApiClient
{
    public function post(string $uri, array $data = []): array;
}


