<?php

namespace Tests\Unit\Services\Users;

use App\Rules\PasswordRule;
use App\Services\Users\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserServicePasswordGenerateTest extends TestCase
{
    use DatabaseTransactions;

    private UserService $service;

    public function test_it_generate_correct_password(): void
    {
        $password = $this->service->createNewPassword();

        $this->assertTrue(
            (new PasswordRule())->passes('password', $password)
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(UserService::class);
    }
}
