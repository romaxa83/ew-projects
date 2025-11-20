<?php

namespace Tests\Unit\Service\Auth;

use App\Services\Auth\PassportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Exceptions\OAuthServerException;
use Tests\Builder\UserBuilder;
use Tests\TestCase;

class PassportServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function check_exception_by_auth(): void
    {
        $service = app(PassportService::class);

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage("Client authentication failed");

        $service->auth('test', 'test', 1, 'test');
    }

    /** @test */
    public function check_exception_by_refresh_token(): void
    {
        $service = app(PassportService::class);

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage("Client authentication failed");

        $service->refreshToken('test', 1, 'test');
    }

    /** @test */
    public function check_revoke_tokens(): void
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $service = app(PassportService::class);

        $this->assertEquals(0, $service->revokeTokens($user->id, 1));
    }
}

