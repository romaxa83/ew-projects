<?php

namespace App\Services\Saas\GPS\Flespi;

interface FlespiClient
{
    public function get(string $uri,  array $query = [], bool $ignoreException = false): array;
}
