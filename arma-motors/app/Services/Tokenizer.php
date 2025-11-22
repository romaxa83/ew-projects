<?php

namespace App\Services;

use App\ValueObjects\Token;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Ramsey\Uuid\Uuid;

class Tokenizer
{
    public function __construct(private CarbonInterval $interval)
    {}

    public function generate(CarbonImmutable $date): Token
    {
        return new Token(
            Uuid::uuid4()->toString(),
            $date->add($this->interval)
        );
    }
}
