<?php

namespace App\Services\AA\Client;

interface RequestClient
{
    public function getRequest(string $uri): array;

    public function getRequestWithoutException(string $uri): array;

    public function postRequest(string $uri, array $data = []): array;

    public function postRequestWithoutException(string $uri, array $data = []): array;
}
