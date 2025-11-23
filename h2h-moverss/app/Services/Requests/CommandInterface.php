<?php

namespace App\Services\Requests;

interface CommandInterface
{
    public function getRequestClient(): RequestClient;

    public function getUri(array $data = null): string;

    public function exec(mixed $data = [], array $headers = []): mixed;
}
