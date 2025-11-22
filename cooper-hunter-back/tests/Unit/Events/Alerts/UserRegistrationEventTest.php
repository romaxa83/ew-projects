<?php

namespace Tests\Unit\Events\Alerts;

use App\Dto\Users\UserDto;
use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertUserEnum;
use App\Models\Admins\Admin;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Services\Users\UserService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class UserRegistrationEventTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use AdminManagerHelperTrait;

    public function test_registration(): void
    {
        $service = resolve(UserService::class);

        $admin = Admin::factory()
            ->create();

        try {
            $user = $service->register(
                UserDto::byArgs(
                    [
                        'first_name' => $this->faker->firstName,
                        'last_name' => $this->faker->lastName,
                        'email' => $this->faker->email,
                        'password' => $this->faker->password,
                        'phone' => $this->faker->e164PhoneNumber,
                        'lang' => 'en'
                    ]
                )
            );
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::USER . '_' . AlertUserEnum::REGISTRATION,
                'model_id' => $user->id,
                'model_type' => $user::MORPH_NAME
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $admin->id,
                'recipient_type' => $admin::MORPH_NAME
            ]
        );
    }
}
