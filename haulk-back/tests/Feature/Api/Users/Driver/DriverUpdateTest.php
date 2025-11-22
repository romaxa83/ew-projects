<?php


namespace Tests\Feature\Api\Users\Driver;


use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Events\ModelChanged;
use App\Models\Files\File;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\PushNotifications\PushNotificationTask;
use App\Models\Users\DriverInfo;
use App\Models\Users\DriverLicense;
use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\DriverFactoryHelper;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DriverUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use OrderFactoryHelper;
    use DriverFactoryHelper;
    use UserFactoryHelper;
    use ElasticsearchClear;
    use OrderESSavingHelper;

    /**
     * @throws Exception
     */
    public function test_it_not_update_driver_only_validate()
    {
        /** @var User $driver */
        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $driver = User::factory()->create($dbAttributes);
        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );
        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['first_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New Full Name';
        $driverInfoAttribute = [
            'notes' => '2345'
        ];
        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->assertDatabaseMissing(DriverInfo::TABLE_NAME, $driverInfoAttribute);
        $this->postJson(
            route('users.update', $driver),
            $newAttributes + $driverInfoAttribute,
            ['validate_only' => true]
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [],
                ]
            );

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->assertDatabaseMissing(DriverInfo::TABLE_NAME, $driverInfoAttribute);
    }

    /**
     * @throws Exception
     */
    public function test_it_update_driver_success()
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
//            'driver_license_number' => $this->faker->randomLetter,
            'notes' => 'b'
        ];

        $role = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);

        $dispatcher = User::query()->onlyDispatchers()->first();

        $role = [
            'role_id' => $role->id,
            'owner_id' => $dispatcher->id,
        ];

        /** @var User $driver */
        $driver = User::factory()->create($dbAttributes);
        $driver->assignRole(User::DRIVER_ROLE);
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
        $newDriverInfoAttributes['notes'] = '4545';
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

    public function test_it_reassign_driver_orders()
    {
        /**@var User $dispatcher*/
        $dispatcher = $this->dispatcherFactory([
            'first_name' => 'Dispatcher',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email
        ]);

        $this->loginAsCarrierDispatcher($dispatcher);

        $driverFrom = $this->getDriver($dispatcher);
        $driverTo = $this->getDriver($dispatcher);

        $this->getDriverInfo($driverFrom);
        $this->getDriverInfo($driverTo);

        // create order
        $order1 = $this->orderFactory(
            [
                'driver_id' => $driverFrom->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => false,
            ]
        );

        $order2 = $this->orderFactory(
            [
                'driver_id' => $driverFrom->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => false,
            ]
        );

        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order1->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driverTo->id
            ]
        );

        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order2->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driverTo->id
            ]
        );

        $this->makeDocuments();
        Event::fake();

        $response = $this->putJson(
            route('reassign.driver-orders', [$driverFrom, $driverTo]),
        );

        Event::assertDispatched(ModelChanged::class, 2);
        Event::assertDispatched(UpdateOrderBroadcast::class, 2);

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order1->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driverTo->id
            ]
        );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order2->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driverTo->id
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'user_id' => $driverTo->id,
                'order_id' => null,
                'type' => 'driver_orders_reassign'
            ]
        );

        $response->assertNoContent();

    }

    public function test_it_reassign_to_previous_driver()
    {
        /**@var User $dispatcher*/
        $dispatcher = $this->dispatcherFactory([
            'first_name' => 'Dispatcher',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email
        ]);

        $this->loginAsCarrierDispatcher($dispatcher);

        $driver = $this->getDriver($dispatcher);

        $this->getDriverInfo($driver);

        // create order
        $order1 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => false,
            ]
        );

        $order2 = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => false,
            ]
        );

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order1->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id
            ]
        );

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order2->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id
            ]
        );

        $this->makeDocuments();

        $response = $this->putJson(
            route('reassign.driver-orders', [$driver, $driver]),
        );

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order1->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id
            ]
        );

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order2->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id
            ]
        );

        $response->assertNoContent();

    }

    public function test_it_reassign_driver_orders_to_another_dispatcher()
    {
        /**@var User $dispatcher1*/
        $dispatcher1 = $this->dispatcherFactory([
            'first_name' => 'Dispatcher',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email
        ]);
        /**@var User $dispatcher2*/
        $dispatcher2 = $this->dispatcherFactory([
            'first_name' => 'Dispatcher',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email
        ]);

        $this->loginAsCarrierDispatcher($dispatcher1);

        $driverFrom = $this->getDriver($dispatcher1);
        $driverTo = $this->getDriver($dispatcher2);

        $this->getDriverInfo($driverFrom);
        $this->getDriverInfo($driverTo);

        // create order
        $this->orderFactory(
            [
                'driver_id' => $driverFrom->id,
                'dispatcher_id' => $dispatcher1->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => false,
            ]
        );

        $this->orderFactory(
            [
                'driver_id' => $driverFrom->id,
                'dispatcher_id' => $dispatcher1->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => false,
            ]
        );

        $this->makeDocuments();

        $response = $this->putJson(
            route('reassign.driver-orders', [$driverFrom, $driverTo]),
        );
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_reassign_driver_orders_without_orders()
    {
        /**@var User $dispatcher*/
        $dispatcher = $this->dispatcherFactory([
            'first_name' => 'Dispatcher',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email
        ]);

        $this->loginAsCarrierDispatcher($dispatcher);

        $driverFrom = $this->getDriver($dispatcher);
        $driverTo = $this->getDriver($dispatcher);

        $this->getDriverInfo($driverFrom);
        $this->getDriverInfo($driverTo);

        $this->makeDocuments();

        $response = $this->putJson(
            route('reassign.driver-orders', [$driverFrom, $driverTo]),
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_reassign_driver_orders_user_not_driver()
    {
        /**@var User $dispatcher*/
        $dispatcher = $this->dispatcherFactory([
            'first_name' => 'Dispatcher',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email
        ]);

        $this->loginAsCarrierDispatcher($dispatcher);

        $driverFrom = $this->userFactory(
            User::DISPATCHER_ROLE,
            [
                'first_name' => 'Dispatcher',
                'last_name' => 'Name',
                'phone' => '1-541-754-3010',
                'email' => $this->faker->email,
                'owner_id' => $dispatcher->id
            ]
        );
        $driverTo = $this->getDriver($dispatcher);

        $this->makeDocuments();

        $response = $this->putJson(
            route('reassign.driver-orders', [$driverFrom, $driverTo]),
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_delete_driver_documents(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $email = $this->faker->email;
        $attributes = [
            'full_name' => 'First Last',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $driversRole = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);

        $roles = [
            'role_id' => $driversRole->id,
            'owner_id' => $this->authenticatedUser->id,
        ];

        $state = factory(State::class)->create();

        $driverInfoAttributes = [
            'notes' => 'test notes',
            'driver_license' => [
                'license_number' => '3434-dfdf',
                'issuing_state_id' => $state->id,
                'issuing_date' => now()->format('m/d/Y'),
                'expiration_date' => now()->addDays(2)->format('m/d/Y'),
                'category' => DriverLicense::CATEGORY_C,
                'category_name' => null,
                DriverLicense::ATTACHED_DOCUMENT_FILED_NAME => UploadedFile::fake()->image('image1.jpg'),
            ],
            'previous_driver_license' => [
                'license_number' => 'sdfsdf343-w',
                'is_usa' => false,
                'issuing_country' => 'Canada',
                'issuing_state_id' => null,
                'issuing_date' => now()->format('m/d/Y'),
                'expiration_date' => now()->addDay()->format('m/d/Y'),
                'category' => DriverLicense::CATEGORY_OTHER,
                'category_name' => 'ABC',
                DriverLicense::ATTACHED_DOCUMENT_FILED_NAME => UploadedFile::fake()->create('file.pdf'),
            ],
            'medical_card' => [
                'card_number' => '23435dfg',
                'issuing_date' => now()->format('m/d/Y'),
                'expiration_date' => now()->addDays(5)->format('m/d/Y'),
                DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME => UploadedFile::fake()->create('file.pdf'),
            ],
            'mvr' => [
                'reported_date' => now()->format('m/d/Y'),
                DriverInfo::ATTACHED_MVR_FILED_NAME => UploadedFile::fake()->image('file.png'),
            ],
            'has_company' => true,
            'company_info' =>  [
                'name' => 'test company',
                'ein' => 'dfsdf654',
                'address' => 'address test',
                'city' => 'cityname',
                'zip' => '23545',
            ],
        ];

        $this->postJson(route('users.update', $driver), $attributes + $driverInfoAttributes + $roles)
            ->assertOk();

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => DriverInfo::class,
                'collection_name' => DriverInfo::ATTACHED_MVR_FILED_NAME,
            ]
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => DriverInfo::class,
                'collection_name' => DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME,
            ]
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => DriverLicense::class,
                'collection_name' => DriverLicense::ATTACHED_DOCUMENT_FILED_NAME,
            ]
        );

        $this->deleteJson(route('users.delete-mvr-document', $driver))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_type' => DriverInfo::class,
                'collection_name' => DriverInfo::ATTACHED_MVR_FILED_NAME,
            ]
        );

        $this->deleteJson(route('users.delete-medical-card-document', $driver))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_type' => DriverInfo::class,
                'collection_name' => DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME,
            ]
        );

        $this->deleteJson(route('users.delete-driver-license-document', $driver))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseCount(
            File::TABLE_NAME,
            1
        );

        $this->deleteJson(route('users.delete-previous-driver-license-document', $driver))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_type' => DriverLicense::class,
                'collection_name' => DriverLicense::ATTACHED_DOCUMENT_FILED_NAME,
            ]
        );
    }
}
