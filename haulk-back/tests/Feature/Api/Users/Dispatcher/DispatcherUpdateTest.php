<?php


namespace Tests\Feature\Api\Users\Dispatcher;


use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Broadcasting\Events\User\UpdateUserBroadcast;
use App\Events\ModelChanged;
use App\Models\Orders\Order;
use App\Models\PushNotifications\PushNotificationTask;
use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\DriverFactoryHelper;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DispatcherUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use UserFactoryHelper;
    use DriverFactoryHelper;
    use OrderFactoryHelper;
    use ElasticsearchClear;
    use OrderESSavingHelper;

    /**
     * @throws Exception
     */
    public function test_it_not_update_only_validate()
    {
        /** @var User $dispatcher */
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
        $dispatcher = User::factory()->create($dbAttributes);
        $dispatcher->assignRole(User::DISPATCHER_ROLE);

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['first_name'] = $this->faker->unique()->name;

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = $newAttributes['first_name'];

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->postJson(route('users.update', $dispatcher->id), $newAttributes, ['validate_only' => true])
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
    public function test_it_update_dispatcher_success()
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

        $role = $this->getRoleRepository()->findByName(User::DISPATCHER_ROLE);

        $roleAttributes = [
            'role_id' => $role->id,
        ];

        /** @var User $dispatcher */
        $dispatcher = User::factory()->create($dbAttributes);
        $dispatcher->assignRole(User::DISPATCHER_ROLE);

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $dispatcher->id,
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

        $this->postJson(route('users.update', $dispatcher->id), $newAttributes + $roleAttributes)
            ->assertOk();

        $this->assertDatabaseHas(User::TABLE_NAME, $newDbAttributes);
    }

    public function test_it_reassign_dispatcher_drivers()
    {
        $this->loginAsCarrierSuperAdmin();
        /**@var User $dispatcherFrom*/
        $dispatcherFrom = $this->dispatcherFactory([
            'first_name' => 'Dispatcher ',
            'last_name' => $this->faker->name,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->email
        ]);

        $driver_1 = $this->getDriver($dispatcherFrom);
        $driver_2 = $this->getDriver($dispatcherFrom);

        $this->getDriverInfo($driver_1);
        $this->getDriverInfo($driver_2);

        // create order
        $order_1 = $this->orderFactory(
            [
                'driver_id' => $driver_1->id,
                'dispatcher_id' => $dispatcherFrom->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => false,
            ]
        );

        $order_2 = $this->orderFactory(
            [
                'driver_id' => $driver_2->id,
                'dispatcher_id' => $dispatcherFrom->id,
                'status' => Order::STATUS_NEW,
                'seen_by_driver' => false,
            ]
        );

        /**@var User $dispatcherTo*/
        $dispatcherTo = $this->dispatcherFactory([
            'first_name' => 'Dispatcher ',
            'last_name' => $this->faker->name,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->email
        ]);


        Event::fake();
        $this->makeDocuments();
        $this->putJson(route('reassign.dispatcher-drivers', ['dispatcherFrom' => $dispatcherFrom->id, 'dispatcherTo' => $dispatcherTo->id]))
            ->assertNoContent();

        Event::assertDispatched(UpdateUserBroadcast::class, 2);
        Event::assertDispatched(UpdateOrderBroadcast::class, 2);
        Event::assertDispatched(ModelChanged::class, 4);

        $driver_1->refresh();
        $driver_2->refresh();
        $order_1->refresh();
        $order_2->refresh();

        $this->assertEquals($driver_1->owner_id,$dispatcherTo->id);
        $this->assertEquals($driver_2->owner_id,$dispatcherTo->id);

        $this->assertEquals($order_1->dispatcher_id,$dispatcherTo->id);
        $this->assertEquals($order_2->dispatcher_id,$dispatcherTo->id);

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'dispatcher_orders_reassign',
                'user_id' => $dispatcherTo->id,
                'order_id' => null
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'dispatcher_drivers_reassign',
                'user_id' => $dispatcherTo->id,
                'order_id' => null
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_reassign_dispatcher',
                'user_id' => $driver_1->id,
                'order_id' => null
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_reassign_dispatcher',
                'user_id' => $driver_2->id,
                'order_id' => null
            ]
        );
    }

    public function test_it_reassign_dispatcher_drivers_without_active_orders()
    {
        $this->loginAsCarrierSuperAdmin();
        /**@var User $dispatcherFrom*/
        $dispatcherFrom = $this->dispatcherFactory([
            'first_name' => 'Dispatcher ',
            'last_name' => $this->faker->name,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->email
        ]);

        $driver_1 = $this->getDriver($dispatcherFrom);
        $driver_2 = $this->getDriver($dispatcherFrom);

        $this->getDriverInfo($driver_1);
        $this->getDriverInfo($driver_2);

        // create order
        $order_1 = $this->orderFactory(
            [
                'driver_id' => $driver_1->id,
                'dispatcher_id' => $dispatcherFrom->id,
                'status' => Order::STATUS_DELIVERED,
                'seen_by_driver' => false,
            ]
        );

        $order_2 = $this->orderFactory(
            [
                'driver_id' => $driver_2->id,
                'dispatcher_id' => $dispatcherFrom->id,
                'status' => Order::STATUS_DELIVERED,
                'seen_by_driver' => false,
            ]
        );

        /**@var User $dispatcherTo*/
        $dispatcherTo = $this->dispatcherFactory([
            'first_name' => 'Dispatcher ',
            'last_name' => $this->faker->name,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->email
        ]);

        Event::fake();

        $this->putJson(route('reassign.dispatcher-drivers', ['dispatcherFrom' => $dispatcherFrom->id, 'dispatcherTo' => $dispatcherTo->id]))
            ->assertNoContent();

        Event::assertDispatched(UpdateUserBroadcast::class, 2);
        Event::assertDispatched(ModelChanged::class);

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'dispatcher_drivers_reassign',
                'user_id' => $dispatcherTo->id
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_reassign_dispatcher',
                'user_id' => $driver_1->id
            ]
        );

        $this->assertDatabaseHas(
            PushNotificationTask::class,
            [
                'type' => 'driver_reassign_dispatcher',
                'user_id' => $driver_2->id
            ]
        );

        $driver_1->refresh();
        $driver_2->refresh();
        $order_1->refresh();
        $order_2->refresh();

        $this->assertEquals($driver_1->owner_id,$dispatcherTo->id);
        $this->assertEquals($driver_2->owner_id,$dispatcherTo->id);

        $this->assertEquals($order_1->dispatcher_id,$dispatcherFrom->id);
        $this->assertEquals($order_2->dispatcher_id,$dispatcherFrom->id);
    }

    public function test_it_reassign_dispatcher_without_drivers()
    {
        $this->loginAsCarrierSuperAdmin();
        /**@var User $dispatcherFrom*/
        $dispatcherFrom = $this->dispatcherFactory([
            'first_name' => 'Dispatcher ',
            'last_name' => $this->faker->name,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->email
        ]);

        /**@var User $dispatcherTo*/
        $dispatcherTo = $this->dispatcherFactory([
            'first_name' => 'Dispatcher ',
            'last_name' => $this->faker->name,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->email
        ]);

        $this->putJson(route('reassign.dispatcher-drivers', ['dispatcherFrom' => $dispatcherFrom->id, 'dispatcherTo' => $dispatcherTo->id]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_reassign_dispatcher_drivers_no_change_dispatcher()
    {
        $this->loginAsCarrierSuperAdmin();
        /**@var User $dispatcherFrom*/
        $dispatcherFrom = $this->dispatcherFactory([
            'first_name' => 'Dispatcher ',
            'last_name' => $this->faker->name,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->email
        ]);

        $driver_1 = $this->getDriver($dispatcherFrom);
        $driver_2 = $this->getDriver($dispatcherFrom);

        $this->getDriverInfo($driver_1);
        $this->getDriverInfo($driver_2);

        // create order
        $order_1 = $this->orderFactory(
            [
                'driver_id' => $driver_1->id,
                'dispatcher_id' => $dispatcherFrom->id,
                'status' => Order::STATUS_DELIVERED,
                'seen_by_driver' => false,
            ]
        );

        $order_2 = $this->orderFactory(
            [
                'driver_id' => $driver_2->id,
                'dispatcher_id' => $dispatcherFrom->id,
                'status' => Order::STATUS_DELIVERED,
                'seen_by_driver' => false,
            ]
        );

        $this->putJson(route('reassign.dispatcher-drivers', ['dispatcherFrom' => $dispatcherFrom->id, 'dispatcherTo' => $dispatcherFrom->id]))
            ->assertNoContent();

        $driver_1->refresh();
        $driver_2->refresh();
        $order_1->refresh();
        $order_2->refresh();

        $this->assertEquals($driver_1->owner_id,$dispatcherFrom->id);
        $this->assertEquals($driver_2->owner_id,$dispatcherFrom->id);

        $this->assertEquals($order_1->dispatcher_id,$dispatcherFrom->id);
        $this->assertEquals($order_2->dispatcher_id,$dispatcherFrom->id);
    }
}
