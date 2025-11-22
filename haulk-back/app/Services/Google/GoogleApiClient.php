<?php

namespace App\Services\Google;

interface GoogleApiClient
{
    public function get(string $uri, string $points): array;
}

