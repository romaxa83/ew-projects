<?php

namespace App\Services\BodyShop\Sync;

interface BSApiClient
{
    public function post(string $uri, array $data);
    public function delete(string $uri, array $data = []);
}

