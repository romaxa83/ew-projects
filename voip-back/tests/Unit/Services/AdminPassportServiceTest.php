<?php

namespace Tests\Unit\Services;

use App\Models\Admins\Admin;
use App\Services\Auth\AdminPassportService;
use App\ValueObjects\Email;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;
use Throwable;

class AdminPassportServiceTest extends TestCase
{
    private AdminPassportService $service;

    /**
     * @throws Throwable
     */
    public function test_it_admin_login_success(): void
    {
        $email = new Email('admin@example.com');
        Admin::factory()->new(['email' => $email])->create();

        $response = $this->service->auth($email, 'Password123');

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
