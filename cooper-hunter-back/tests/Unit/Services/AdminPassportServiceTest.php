<?php

namespace Tests\Unit\Services;

use App\Models\Admins\Admin;
use App\Services\Auth\AdminPassportService;
use App\ValueObjects\Email;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class AdminPassportServiceTest extends TestCase
{
    use DatabaseTransactions;

    private AdminPassportService $service;

    /**
     * @throws Throwable
     */
    public function test_it_admin_login_success()
    {
        $email = new Email('admin@example.com');
        Admin::factory()->new(['email' => $email])->create();

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

        $this->service = $this->app->make(AdminPassportService::class);
    }
}
