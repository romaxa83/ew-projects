<?php

namespace Tests\Unit\ValueObject;

use App\ValueObjects\Token;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class TokenTest extends TestCase
{
    /** @test */
    public function create()
    {
        $token = new Token(
            $value = Uuid::uuid4()->toString(),
            $date = CarbonImmutable::now()
        );

        $this->assertEquals($token->getValue(), $value);
        $this->assertEquals($token->getExpires(), $date);
        $this->assertNotEquals($token->getExpires(), CarbonImmutable::now());
        $this->assertFalse($token->isEmpty());
    }

    /** @test */
    public function create_incorrect()
    {
        $this->expectException(InvalidArgumentException::class);
        new Token('wwwwww', CarbonImmutable::now());
    }

    /** @test */
    public function create_empty()
    {
        $this->expectException(InvalidArgumentException::class);
        new Token('', CarbonImmutable::now());
    }

    /** @test */
    public function expires()
    {
        $token = new Token(
            $value = Uuid::uuid4()->toString(),
            $date = CarbonImmutable::now()
        );

        $this->assertFalse($token->isExpiredTo($date->modify('-1 sec')));
        $this->assertTrue($token->isExpiredTo($date));
        $this->assertTrue($token->isExpiredTo($date->modify('+1 sec')));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_validate_success()
    {
        $token = new Token(
            $value = Uuid::uuid4()->toString(),
            $date = CarbonImmutable::now()
        );

        $token->validate($value, $date->modify('-1 secs'));
    }

    /** @test */
    public function validate_wrong()
    {
        $token = new Token(
            $value = Uuid::uuid4()->toString(),
            $date = CarbonImmutable::now()
        );

        $this->expectExceptionMessage('Token is invalid.');
        $token->validate(Uuid::uuid4()->toString(), $date->modify('-1 secs'));
    }

    /** @test */
    public function validate_expired()
    {
        $token = new Token(
            $value = Uuid::uuid4()->toString(),
            $date = CarbonImmutable::now()
        );

        $this->expectExceptionMessage('Token is expired.');
        $token->validate($value, $date->modify('+1 secs'));
    }
}

