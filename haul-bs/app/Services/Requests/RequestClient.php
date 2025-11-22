<?php

namespace App\Services\Requests;

interface RequestClient
{
    public function get(string $uri, array $query = [], array $headers = []): array;

    public function post(string $uri, array $data = [], array $headers = []): array;

    public function put(string $uri, array $data = [], array $headers = []): array;

    public function putAsync(string $uri, array $data = [], array $headers = []): array;

    public function delete(string $uri, array $headers = []): array;
}
