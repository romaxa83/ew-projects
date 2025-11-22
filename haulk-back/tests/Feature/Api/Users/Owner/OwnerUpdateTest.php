<?php

namespace Tests\Feature\Api\Users\Owner;

use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\DriverFactoryHelper;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OwnerUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use OrderFactoryHelper;
    use DriverFactoryHelper;
    use UserFactoryHelper;

    public function test_it_update_owner_success(): void
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


        $role = $this->getRoleRepository()->findByName(User::OWNER_ROLE);

        $role = [
            'role_id' => $role->id,
        ];

        /** @var User $owner */
        $owner = User::factory()->create($dbAttributes);
        $owner->assignRole(User::OWNER_ROLE);
        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);

        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $owner->id,
                'model_type' => User::class,
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);

        $this->postJson(
            route('users.update', $owner),
            $newAttributes + $role
        )
            ->assertOk();

        $this->assertDatabaseHas(User::TABLE_NAME, $newDbAttributes);
    }

    public function test_validation_error_assigned_vehicles(): void
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


        $role = $this->getRoleRepository()->findByName(User::ACCOUNTANT_ROLE);

        $role = [
            'role_id' => $role->id,
        ];

        /** @var User $owner */
        $owner = User::factory()->create($dbAttributes);
        $owner->assignRole(User::OWNER_ROLE);
        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);

        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $owner->id,
                'model_type' => User::class,
            ]
        );

        factory(Truck::class)->create(['owner_id' => $owner->id]);

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);

        $this->postJson(
            route('users.update', $owner),
            $newAttributes + $role
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
    }

    public function test_it_update_owner_with_tags_success(): void
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


        $role = $this->getRoleRepository()->findByName(User::OWNER_ROLE);

        $role = [
            'role_id' => $role->id,
        ];

        /** @var User $owner */
        $owner = User::factory()->create($dbAttributes);
        $owner->assignRole(User::OWNER_ROLE);
        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);

        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $owner->id,
                'model_type' => User::class,
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $tag = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER]);

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);

        $this->postJson(
            route('users.update', $owner),
            $newAttributes + $role + ['tags' => [$tag->id]]
        )
            ->assertOk();

        $this->assertDatabaseHas(User::TABLE_NAME, $newDbAttributes);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $owner->id,
            'taggable_type' => User::class,
        ]);
    }
}
