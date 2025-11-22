<?php

namespace App\Services\OneC\Client;

interface RequestClient
{
    public function getRequest(string $uri): array;

    public function postRequest(string $uri, array $data = []): array;
}
