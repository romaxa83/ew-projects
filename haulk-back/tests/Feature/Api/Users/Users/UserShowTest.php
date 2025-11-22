<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_user_for_unauthorized_users()
    {
        $user = User::factory()->create();
        $user->assignRole(User::ACCOUNTANT_ROLE);

        $this->getJson(route('users.show', $user))->assertUnauthorized();
    }

    public function test_it_not_show_user_for_not_permitted_users()
    {
        $user = User::factory()->create();
        $user->assignRole(User::ACCOUNTANT_ROLE);

        $this->loginAsCarrierDispatcher();

        $this->getJson(route('users.show', $user))
            ->assertOk();
    }

    public function test_it_show_user_for_permitted_users()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(User::ACCOUNTANT_ROLE);

        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('users.show', $user))
            ->assertOk()
            ->assertJsonStructure(['data' => ['full_name', 'email', 'role_id', 'phone']]);
    }

    public function test_it_show_additional_fields_for_driver()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(User::DRIVER_ROLE);

        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('users.show', $user))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'full_name',
                        'email',
                        'role_id',
                        'phone',
                        'owner_id',
                    ]
                ]
            );
    }

    public function test_it_show_truck_trailer_fields_for_driver()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(User::DRIVER_ROLE);

        factory(Truck::class)->create(['driver_id' => $user->id]);
        factory(Trailer::class)->create(['driver_id' => $user->id]);

        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('users.show', $user))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'truck' => [
                            'id',
                        ],
                        'trailer' => [
                            'id',
                        ],
                    ]
                ]
            );
    }
}
