<?php

namespace App\Contracts\Exceptions\Http;

interface ApiAware
{
    public function getHttpCode(): int;

    public function getMessage(): string;

    public function getCategory(): string;
}
