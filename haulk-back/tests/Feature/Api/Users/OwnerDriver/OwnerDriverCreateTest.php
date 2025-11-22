<?php

namespace Tests\Feature\Api\Users\OwnerDriver;

use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OwnerDriverCreateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_create_new_owner_driver_success(): void
    {
        $this->withoutExceptionHandling();
        $this->loginAsCarrierSuperAdmin();

        $email = $this->faker->email;
        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $driverInfoAttributes = [
            'notes' => $this->faker->randomLetter,
        ];

        $driversRole = $this->getRoleRepository()->findByName(User::OWNER_DRIVER_ROLE);

        $roles = [
            'role_id' => $driversRole->id,
            'owner_id' => $this->authenticatedUser->id,
        ];


        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes + ['owner_id' => $this->authenticatedUser->id]);
        $this->assertDatabaseMissing(DriverInfo::TABLE_NAME, $driverInfoAttributes);

        $this->postJson(route('users.store'), $attributes + $driverInfoAttributes + $roles)
            ->assertCreated();

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes + ['owner_id' => $this->authenticatedUser->id]);
        $this->assertDatabaseHas(DriverInfo::TABLE_NAME, $driverInfoAttributes);
    }
}
