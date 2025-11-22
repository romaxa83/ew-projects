<?php

namespace Tests\Feature\Mutations\FrontOffice\Drivers;

use App\Enums\Utilities\MorphModelNameEnum;
use App\GraphQL\Mutations\FrontOffice\Drivers\DriverCreateMutation;
use App\Models\Clients\Client;
use App\Models\Drivers\Driver;
use App\Models\Phones\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DriverCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsUserWithRole();
    }

    public function test_create_driver(): void
    {
        $client = Client::factory()
            ->create();

        $driver = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'second_name' => $this->faker->lastName,
            'phones' => [
                [
                    'phone' => $this->faker->ukrainianPhone,
                ]
            ],
            'email' => $this->faker->safeEmail,
            'comment' => $this->faker->text,
            'client_id' => $client->id,
        ];

        $driverId = $this->postGraphQL(
            GraphQLQuery::mutation(DriverCreateMutation::NAME)
                ->args(
                    [
                        'driver' => $driver
                    ]
                )
                ->select(
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'second_name',
                        'phone',
                        'phones' => [
                            'is_default',
                            'phone'
                        ],
                        'email',
                        'comment',
                        'client' => [
                            'id',
                        ],
                        'is_moderated',
                        'active'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        DriverCreateMutation::NAME => [
                            'first_name' => $driver['first_name'],
                            'last_name' => $driver['last_name'],
                            'second_name' => $driver['second_name'],
                            'phone' => $driver['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'is_default' => true,
                                    'phone' => $driver['phones'][0]['phone'],
                                ]
                            ],
                            'email' => $driver['email'],
                            'comment' => $driver['comment'],
                            'client' => [
                                'id' => $client->id,
                            ],
                            'is_moderated' => false,
                            'active' => true,
                        ]
                    ]
                ]
            )
            ->json('data.' . DriverCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Driver::class,
            [
                'id' => $driverId
            ]
        );

        $this->assertDatabaseHas(
            Phone::class,
            [
                'owner_type' => MorphModelNameEnum::driver()->key,
                'owner_id' => $driverId
            ]
        );
    }

    public function test_create_driver_without_client(): void
    {
        $driverId = $this->postGraphQL(
            GraphQLQuery::mutation(DriverCreateMutation::NAME)
                ->args(
                    [
                        'driver' => [
                            'first_name' => $this->faker->firstName,
                            'last_name' => $this->faker->lastName,
                            'second_name' => $this->faker->lastName,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                ]
                            ],
                            'email' => $this->faker->safeEmail,
                            'comment' => $this->faker->text,
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'client' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        DriverCreateMutation::NAME => [
                            'client' => null,
                        ]
                    ]
                ]
            )
            ->json('data.' . DriverCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Driver::class,
            [
                'id' => $driverId
            ]
        );
    }

    public function test_try_create_driver_with_not_unique_fio(): void
    {
        $driver = Driver::factory()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::mutation(DriverCreateMutation::NAME)
                ->args(
                    [
                        'driver' => [
                            'first_name' => $driver->first_name,
                            'last_name' => $driver->last_name,
                            'second_name' => $driver->second_name,
                            'phones' => [
                                [
                                    'phone' => $driver->phone->phone,
                                ]
                            ],
                            'email' => $this->faker->safeEmail,
                            'comment' => $this->faker->text,
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.drivers.uniq')
                        ]
                    ]
                ]
            );
    }

    public function test_try_create_driver_with_not_unique_email(): void
    {
        $driver = Driver::factory()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::mutation(DriverCreateMutation::NAME)
                ->args(
                    [
                        'driver' => [
                            'first_name' => $this->faker->firstName,
                            'last_name' => $this->faker->lastName,
                            'second_name' => $this->faker->lastName,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                ]
                            ],
                            'email' => $driver->email,
                            'comment' => $this->faker->text,
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.drivers.uniq')
                        ]
                    ]
                ]
            );
    }

    public function test_create_driver_with_not_unique_fio_and_not_active(): void
    {
        $driver = Driver::factory(['active' => false])
            ->create();

        $this->postGraphQL(
            GraphQLQuery::mutation(DriverCreateMutation::NAME)
                ->args(
                    [
                        'driver' => [
                            'first_name' => $driver->first_name,
                            'last_name' => $driver->last_name,
                            'second_name' => $driver->second_name,
                            'phones' => [
                                [
                                    'phone' => $driver->phone->phone,
                                ]
                            ],
                            'email' => $driver->email,
                            'comment' => $this->faker->text,
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'is_moderated',
                        'active'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        DriverCreateMutation::NAME => [
                            'id' => $driver->id,
                            'is_moderated' => false,
                            'active' => true,
                        ]
                    ]
                ]
            );
    }
}
