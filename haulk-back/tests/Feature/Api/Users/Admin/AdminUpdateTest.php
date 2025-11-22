<?php

namespace Tests\Feature\Api\Users\Admin;

use App\Models\Forms\Draft;
use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_it_not_update_only_validate()
    {
        /** @var User $admin */
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

        $admin = User::factory()->create($dbAttributes);

        $admin->assignRole(User::ADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['first_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);

        $this->postJson(
            route('users.update', $admin),
            $newAttributes,
            [config('requestvalidationonly.header_key') => true]
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [],
                ]
            );

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
    }

    /**
     * @throws Exception
     */
    public function test_it_update_admin_success()
    {
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

        $role = $this->getRoleRepository()->findByName(User::ADMIN_ROLE);

        $roleAttributes = [
            'role_id' => $role->id,
        ];

        /** @var User $admin */
        $admin = User::factory()->create($dbAttributes);
        $admin->assignRole(User::ADMIN_ROLE);

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $admin->id,
                'model_type' => User::class,
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = [
            'first_name' => 'New',
            'last_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);

        $this->postJson(route('users.update', $admin), $newAttributes + $roleAttributes)
            ->assertOk();

        $this->assertDatabaseHas(User::TABLE_NAME, $newDbAttributes);
    }

    public function test_validate_update_admin_with_draft_store()
    {
        $email = $this->faker->email;

        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        /** @var User $admin */
        $admin = User::factory()->create($dbAttributes);

        $admin->assignRole(User::ADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin();

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $path = route('users.update', $admin);
        $this->assertDatabaseMissing(
            Draft::TABLE_NAME,
            [
                'path' => $path,
                'user_id' => $this->authenticatedUser->id,
            ]
        );

        $this->postJson(
            route('users.update', $admin),
            $newAttributes,
            [
                config('draft.header_key') => $path,
                config('requestvalidationonly.header_key') => true,
            ]
        )->assertOk();

        $this->assertDatabaseHas(
            Draft::TABLE_NAME,
            [
                'path' => $path,
                'user_id' => $this->authenticatedUser->id,
            ]
        );
    }
}
