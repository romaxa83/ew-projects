<?php

namespace Tests\Unit\Services;

use App\Models\Users\User;
use App\Services\Auth\UserPassportService;
use App\ValueObjects\Email;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class UserPassportServiceTest extends TestCase
{

    use DatabaseTransactions;

    private UserPassportService $service;

    /**
     * @throws Throwable
     */
    public function test_it_user_login_success()
    {
        $email = new Email('user@example.com');
        User::factory()->create(['email' => $email]);

        $this->assertUsersHas(['email' => $email]);

        $response = $this->service->auth($email, 'password');

        self::assertArrayHasKey('token_type', $response);
        self::assertArrayHasKey('expires_in', $response);
        self::assertArrayHasKey('access_token', $response);
        self::assertArrayHasKey('refresh_token', $response);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();

        $this->service = $this->app->make(UserPassportService::class);
    }
}
