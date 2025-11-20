<?php

namespace App\Services\ARI;

interface ClientARI
{
    public function get(string $uri): array;

    public function post(string $uri);

    public function delete(string $uri);
}


