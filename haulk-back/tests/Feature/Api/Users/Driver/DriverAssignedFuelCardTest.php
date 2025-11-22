<?php

namespace Feature\Api\Users\Driver;

use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Events\ModelChanged;
use App\Models\Files\File;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
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

class DriverAssignedFuelCardTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use OrderFactoryHelper;
    use DriverFactoryHelper;
    use UserFactoryHelper;

    /**
     * @throws Exception
     */
    public function test_it_success()
    {
        $fuelCard = FuelCard::factory()->create();
        /** @var User $driver */
        $attributes = [
            'fuel_card_id' => $fuelCard->id,
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

        $this->putJson(
            route('users.assigned-fuel-card', $driver),
            $attributes,
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'fuel_cards' => [
                            [
                                'id' => $fuelCard->id,
                                'active' => $fuelCard->id,
                            ]
                        ]
                    ],
                ]
            );

        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'user_id' => $driver->id,
            'active' => true,
            'fuel_card_id' => $fuelCard->id,
        ]);
    }

    public function test_it_success_add_two_card()
    {
        $fuelCard = FuelCard::factory()->create();
        /** @var User $driver */
        $attributes = [
            'fuel_card_id' => $fuelCard->id,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $driver = User::factory()->create($dbAttributes);
        $fuelCard2 = FuelCard::factory()->create();
        FuelCardHistory::factory()->for($driver)->for($fuelCard2)->create(['active' => true, 'date_assigned' => now()]);
        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );
        $this->loginAsCarrierSuperAdmin();

        $this->putJson(
            route('users.assigned-fuel-card', $driver),
            $attributes,
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'fuel_cards' => [
                            [
                                'id' => $fuelCard2->id,
                                'active' => true,
                            ],
                            [
                                'id' => $fuelCard->id,
                                'active' => true,
                            ]
                        ]
                    ],
                ]
            );

        $this->assertDatabaseCount(FuelCardHistory::TABLE_NAME, 2);

        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'active' => true,
            'fuel_card_id' => $fuelCard2->id,
            'user_id' => $driver->id,
        ]);

        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'user_id' => $driver->id,
            'active' => true,
            'fuel_card_id' => $fuelCard->id,
        ]);
    }

    public function test_it_success_add_three_card()
    {
        $fuelCard = FuelCard::factory()->create();
        /** @var User $driver */
        $attributes = [
            'fuel_card_id' => $fuelCard->id,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $driver = User::factory()->create($dbAttributes);

        $fuelCard2 = FuelCard::factory()->create();
        FuelCardHistory::factory()->for($driver)->for($fuelCard2)->create(['active' => true, 'date_assigned' => now()]);

        $fuelCard3 = FuelCard::factory()->create();
        FuelCardHistory::factory()->for($driver)->for($fuelCard3)->create(['active' => true, 'date_assigned' => now()]);

        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );
        $this->loginAsCarrierSuperAdmin();

        $this->putJson(
            route('users.assigned-fuel-card', $driver),
            $attributes,
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'fuel_cards' => [
                            [
                                'id' => $fuelCard3->id,
                                'active' => true,
                            ],
                            [
                                'id' => $fuelCard->id,
                                'active' => true,
                            ]
                        ]
                    ],
                ]
            );

        $this->assertDatabaseCount(FuelCardHistory::TABLE_NAME, 3);

        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'active' => false,
            'fuel_card_id' => $fuelCard2->id,
            'user_id' => $driver->id,
        ]);

        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'active' => true,
            'fuel_card_id' => $fuelCard3->id,
            'user_id' => $driver->id,
        ]);

        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'user_id' => $driver->id,
            'active' => true,
            'fuel_card_id' => $fuelCard->id,
        ]);
    }

    public function test_it_success_unassigned_other_driver()
    {
        /** @var User $driver */

        $driver = User::factory()->create([
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ]);

        $fuelCard = FuelCard::factory()->create();
        FuelCardHistory::factory()->for($driver)->for($fuelCard)->create(['active' => true, 'date_assigned' => now()]);

        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );

        $attributes = [
            'fuel_card_id' => $fuelCard->id,
        ];

        $driverOther = User::factory()->create([
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => 'testtest@gmail.com',
        ]);
        $driverOther->assignRole(User::DRIVER_ROLE);


        $this->loginAsCarrierSuperAdmin();

        $this->putJson(
            route('users.assigned-fuel-card', $driverOther),
            $attributes,
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'fuel_cards' => [
                            [
                                'id' => $fuelCard->id,
                                'active' => true,
                            ],
                        ]
                    ],
                ]
            );

        $this->assertDatabaseCount(FuelCardHistory::TABLE_NAME, 2);

        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'active' => false,
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
        ]);

        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'active' => true,
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driverOther->id,
        ]);
    }
}
