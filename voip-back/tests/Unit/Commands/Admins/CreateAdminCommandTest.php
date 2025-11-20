<?php

namespace Tests\Unit\Commands\Admins;

use App\Console\Commands\Admins\CreateAdminCommand;
use App\Models\Admins\Admin;
use InvalidArgumentException;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    public function test_command_create_new_admin(): void
    {
        $name = 'New admin name';
        $email = 'new.admin.email@example.com';
        $password = 'password';

        $attributes = [
            'name' => $name,
            'email' => $email,
        ];

        $this->assertDatabaseMissing(Admin::TABLE, $attributes);

        $this->artisan(CreateAdminCommand::class)
            ->expectsQuestion(CreateAdminCommand::QUESTION_NAME, $name)
            ->expectsQuestion(CreateAdminCommand::QUESTION_EMAIL, $email)
            ->expectsQuestion(CreateAdminCommand::QUESTION_PASSWORD, $password)//
            ->assertExitCode(CreateAdminCommand::SUCCESS);

        $this->assertDatabaseHas(Admin::TABLE, $attributes);
    }

    public function test_command_thor_error_when_email_is_incorrect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(__('exceptions.value_must_be_email'));

        $this->artisan(CreateAdminCommand::class)
            ->expectsQuestion(CreateAdminCommand::QUESTION_NAME, 'name')
            ->expectsQuestion(CreateAdminCommand::QUESTION_EMAIL, 'incorrect@email')
            ->expectsQuestion(CreateAdminCommand::QUESTION_PASSWORD, 'password')
            ->assertExitCode(CreateAdminCommand::FAILURE);
    }
}
