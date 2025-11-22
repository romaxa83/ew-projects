<?php

namespace Tests\Feature\V2\Users\Profile;

use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_it_not_update_profile_only_validate()
    {
        $attributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $user = User::factory()->create($attributes);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin($user);

        $newAttributes = $attributes;
        $newAttributes['first_name'] = 'Full Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newAttributes);
        $this->putJson(route('v2.carrier.profile.update'), $newAttributes, ['validate_only' => true])
            ->assertOk()
            ->assertJson(
                [
                    'data' => [],
                ]
            );

        $this->assertDatabaseMissing(User::TABLE_NAME, $newAttributes);
    }

    /**
     * @throws Exception
     */
    public function test_it_update_profile_success()
    {
        $attributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin($user);

        $this->assertDatabaseHas(User::TABLE_NAME, $attributes);
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $user->id,
                'model_type' => User::class,
            ]
        );

        $newAttributes = $attributes;
        $newAttributes['first_name'] = 'New';
        $newAttributes['last_name'] = 'Full Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newAttributes);
        $this->putJson(route('v2.carrier.profile.update'), $newAttributes)
            ->assertOk();
        $this->assertDatabaseHas(User::TABLE_NAME, $newAttributes);
    }
}
