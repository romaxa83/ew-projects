<?php

namespace Tests\Unit\Commands\Admins;

use App\Console\Commands\Admins\CreateAdminCommand;
use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_command_create_new_admin(): void
    {
        $attributes = [
            CreateAdminCommand::QUESTION_FIRST_NAME => $this->faker->firstName,
            CreateAdminCommand::QUESTION_LAST_NAME => $this->faker->lastName,
            CreateAdminCommand::QUESTION_EMAIL => $this->faker->safeEmail,
            CreateAdminCommand::QUESTION_PHONE => $this->faker->ukrainianPhone,
            CreateAdminCommand::QUESTION_PASSWORD => $this->faker->password(8),
        ];

        $this->artisan(
            CreateAdminCommand::class,
        )
            ->expectsQuestion(
                CreateAdminCommand::QUESTION_FIRST_NAME,
                $attributes[CreateAdminCommand::QUESTION_FIRST_NAME]
            )
            ->expectsQuestion(
                CreateAdminCommand::QUESTION_LAST_NAME,
                $attributes[CreateAdminCommand::QUESTION_LAST_NAME]
            )
            ->expectsQuestion(CreateAdminCommand::QUESTION_EMAIL, $attributes[CreateAdminCommand::QUESTION_EMAIL])
            ->expectsQuestion(CreateAdminCommand::QUESTION_PHONE, $attributes[CreateAdminCommand::QUESTION_PHONE])
            ->expectsQuestion(CreateAdminCommand::QUESTION_PASSWORD, $attributes[CreateAdminCommand::QUESTION_PASSWORD])
            ->assertExitCode(CreateAdminCommand::SUCCESS);

        $this->assertDatabaseHas(
            Admin::class,
            [
                'email' => $attributes[CreateAdminCommand::QUESTION_EMAIL],
            ]
        );
    }

    public function test_command_thor_error_when_email_is_incorrect(): void
    {
        $this->expectException(ValidationException::class);

        $attributes = [
            CreateAdminCommand::QUESTION_FIRST_NAME => $this->faker->firstName,
            CreateAdminCommand::QUESTION_LAST_NAME => $this->faker->lastName,
            CreateAdminCommand::QUESTION_EMAIL => $this->faker->lexify,
            CreateAdminCommand::QUESTION_PHONE => $this->faker->ukrainianPhone,
            CreateAdminCommand::QUESTION_PASSWORD => $this->faker->password(8),
        ];

        $this->artisan(
            CreateAdminCommand::class,
        )
            ->expectsQuestion(
                CreateAdminCommand::QUESTION_FIRST_NAME,
                $attributes[CreateAdminCommand::QUESTION_FIRST_NAME]
            )
            ->expectsQuestion(
                CreateAdminCommand::QUESTION_LAST_NAME,
                $attributes[CreateAdminCommand::QUESTION_LAST_NAME]
            )
            ->expectsQuestion(CreateAdminCommand::QUESTION_EMAIL, $attributes[CreateAdminCommand::QUESTION_EMAIL])
            ->expectsQuestion(CreateAdminCommand::QUESTION_PHONE, $attributes[CreateAdminCommand::QUESTION_PHONE])
            ->expectsQuestion(CreateAdminCommand::QUESTION_PASSWORD, $attributes[CreateAdminCommand::QUESTION_PASSWORD])
            ->assertExitCode(CreateAdminCommand::FAILURE);
    }


}
