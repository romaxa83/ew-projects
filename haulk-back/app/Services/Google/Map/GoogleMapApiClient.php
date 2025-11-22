<?php

namespace App\Services\Google\Map;

interface GoogleMapApiClient
{
    public function get(string $uri, array $params = []): array;
}
