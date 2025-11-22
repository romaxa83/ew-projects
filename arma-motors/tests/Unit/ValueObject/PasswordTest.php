<?php

namespace Tests\Unit\ValueObject;

use App\DTO\Admin\AdminDTO;
use App\ValueObjects\Email;
use App\ValueObjects\Password;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    /** @test */
    public function success_random()
    {
        \Config::set('admin.password.random', true);

        $password = new Password;

        $this->assertNotEmpty($password);
        $this->assertNotEquals($password, Password::DEFAULT);
    }

    /** @test */
    public function success_not_random()
    {
        \Config::set('admin.password.random', false);

        $password = new Password;

        $this->assertNotEmpty($password);
        $this->assertEquals($password, Password::DEFAULT);
    }
}
