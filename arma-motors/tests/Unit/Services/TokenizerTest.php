<?php

namespace Tests\Unit\Services;

use App\Services\Tokenizer;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Tests\TestCase;

class TokenizerTest extends TestCase
{
    /** @test */
    public function success()
    {
        $interval = new CarbonInterval('PT1H');
        $date = new CarbonImmutable('+1 day');
        $tokenizer = new Tokenizer($interval);

        $token = $tokenizer->generate($date);

        $this->assertEquals($date->add($interval), $token->getExpires());
    }
}


