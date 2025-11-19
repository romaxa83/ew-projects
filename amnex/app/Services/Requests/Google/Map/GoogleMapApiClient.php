<?php

namespace App\Services\Requests\Google\Map;

interface GoogleMapApiClient
{
    public function get(string $uri, array $params = []): array;
}
