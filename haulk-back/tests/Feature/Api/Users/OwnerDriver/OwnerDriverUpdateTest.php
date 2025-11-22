<?php

namespace Tests\Feature\Api\Users\OwnerDriver;

use App\Models\Tags\Tag;
use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\DriverFactoryHelper;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OwnerDriverUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use OrderFactoryHelper;
    use DriverFactoryHelper;
    use UserFactoryHelper;

    public function test_it_update_owner_driver_success(): void
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

        $driverInfoAttributes = [
            'notes' => 'b'
        ];

        $role = $this->getRoleRepository()->findByName(User::OWNER_DRIVER_ROLE);

        $dispatcher = User::query()->onlyDispatchers()->first();

        $role = [
            'role_id' => $role->id,
            'owner_id' => $dispatcher->id,
        ];

        /** @var User $driver */
        $driver = User::factory()->create($dbAttributes);
        $driver->assignRole(User::OWNER_DRIVER_ROLE);
        $driverInfo = DriverInfo::factory()->create(
            [
                'driver_id' => $driver->id,
            ] + $driverInfoAttributes
        );
        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas(
            DriverInfo::TABLE_NAME,
            [
                'id' => $driverInfo->id,
                'driver_id' => $driver->id,
            ] + $driverInfoAttributes
        );
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $driver->id,
                'model_type' => User::class,
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $newDriverInfoAttributes = $driverInfoAttributes;
        $newDriverInfoAttributes['notes'] = 'werwe';
        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->assertDatabaseMissing(
            DriverInfo::TABLE_NAME,
            $newDriverInfoAttributes
        );
        $this->postJson(
            route('users.update', $driver),
            $newAttributes + $newDriverInfoAttributes + $role
        )
            ->assertOk();

        $this->assertDatabaseHas(User::TABLE_NAME, $newDbAttributes);
        $this->assertDatabaseHas(
            DriverInfo::TABLE_NAME,
            $newDriverInfoAttributes
        );
    }

    public function test_it_update_owner_driver_success_with_owner_vehicles_to_another_role(): void
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

        $driverInfoAttributes = [
            'notes' => 'b'
        ];

        $role = $this->getRoleRepository()->findByName(User::ACCOUNTANT_ROLE);

        $role = [
            'role_id' => $role->id,
        ];

        /** @var User $driver */
        $driver = User::factory()->create($dbAttributes);
        $driver->assignRole(User::OWNER_DRIVER_ROLE);
        $driverInfo = DriverInfo::factory()->create(
            [
                'driver_id' => $driver->id,
            ] + $driverInfoAttributes
        );
        factory(Truck::class)->create(['owner_id' => $driver->id]);
        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas(
            DriverInfo::TABLE_NAME,
            [
                'id' => $driverInfo->id,
                'driver_id' => $driver->id,
            ] + $driverInfoAttributes
        );
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $driver->id,
                'model_type' => User::class,
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $newDriverInfoAttributes = $driverInfoAttributes;
        $newDriverInfoAttributes['notes'] = '234';
        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->assertDatabaseMissing(
            DriverInfo::TABLE_NAME,
            $newDriverInfoAttributes
        );
        $this->postJson(
            route('users.update', $driver),
            $newAttributes + $newDriverInfoAttributes + $role
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
    }

    public function test_it_update_owner_driver_success_with_driver_vehicles_to_another_role(): void
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

        $driverInfoAttributes = [
            'notes' => 'b'
        ];

        $role = $this->getRoleRepository()->findByName(User::ACCOUNTANT_ROLE);

        $role = [
            'role_id' => $role->id,
        ];

        /** @var User $driver */
        $driver = User::factory()->create($dbAttributes);
        $driver->assignRole(User::OWNER_DRIVER_ROLE);
        $driverInfo = DriverInfo::factory()->create(
            [
                'driver_id' => $driver->id,
            ] + $driverInfoAttributes
        );
        factory(Truck::class)->create(['driver_id' => $driver->id]);
        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas(
            DriverInfo::TABLE_NAME,
            [
                'id' => $driverInfo->id,
                'driver_id' => $driver->id,
            ] + $driverInfoAttributes
        );
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $driver->id,
                'model_type' => User::class,
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $newDriverInfoAttributes = $driverInfoAttributes;
        $newDriverInfoAttributes['notes'] = '345';
        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->assertDatabaseMissing(
            DriverInfo::TABLE_NAME,
            $newDriverInfoAttributes
        );
        $this->postJson(
            route('users.update', $driver),
            $newAttributes + $newDriverInfoAttributes + $role
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
    }

    public function test_it_update_owner_driver_with_tags_success(): void
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

        $driverInfoAttributes = [
            'notes' => 'b'
        ];

        $role = $this->getRoleRepository()->findByName(User::OWNER_DRIVER_ROLE);

        $dispatcher = User::query()->onlyDispatchers()->first();

        $role = [
            'role_id' => $role->id,
            'owner_id' => $dispatcher->id,
        ];

        /** @var User $driver */
        $driver = User::factory()->create($dbAttributes);
        $driver->assignRole(User::OWNER_DRIVER_ROLE);
        $driverInfo = DriverInfo::factory()->create(
            [
                'driver_id' => $driver->id,
            ] + $driverInfoAttributes
        );
        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas(
            DriverInfo::TABLE_NAME,
            [
                'id' => $driverInfo->id,
                'driver_id' => $driver->id,
            ] + $driverInfoAttributes
        );
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $driver->id,
                'model_type' => User::class,
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $newDriverInfoAttributes = $driverInfoAttributes;
        $newDriverInfoAttributes['notes'] = '364';

        $tag = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER]);

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->assertDatabaseMissing(
            DriverInfo::TABLE_NAME,
            $newDriverInfoAttributes
        );
        $this->postJson(
            route('users.update', $driver),
            $newAttributes + $newDriverInfoAttributes + $role + ['tags' => [$tag->id]]
        )
            ->assertOk();

        $this->assertDatabaseHas(User::TABLE_NAME, $newDbAttributes);
        $this->assertDatabaseHas(
            DriverInfo::TABLE_NAME,
            $newDriverInfoAttributes
        );
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $driver->id,
            'taggable_type' => User::class,
        ]);
    }
}
