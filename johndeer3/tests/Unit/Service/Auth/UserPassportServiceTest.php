<?php

namespace Tests\Unit\Service\Auth;

use App\Services\Auth\PassportService;
use App\Services\Auth\UserPassportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Exceptions\OAuthServerException;
use Tests\Builder\UserBuilder;
use Tests\TestCase;

class UserPassportServiceTest extends TestCase
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
    public function check_logout(): void
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $service = app(UserPassportService::class);

        $this->assertFalse($service->logout($user));
    }
}

