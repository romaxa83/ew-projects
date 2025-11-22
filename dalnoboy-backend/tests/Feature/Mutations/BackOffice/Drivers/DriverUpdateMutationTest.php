<?php

namespace Tests\Feature\Mutations\BackOffice\Drivers;

use App\GraphQL\Mutations\BackOffice\Drivers\DriverUpdateMutation;
use App\Models\Clients\Client;
use App\Models\Drivers\Driver;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DriverUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_driver(): void
    {
        $driver = Driver::factory()
            ->withoutClient()
            ->create();

        $client = Client::factory()
            ->create();

        $driverData = [
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
            'client_id' => $client->id
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(DriverUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $driver->id,
                        'driver' => $driverData
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
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        DriverUpdateMutation::NAME => [
                            'id' => $driver->id,
                            'first_name' => $driverData['first_name'],
                            'last_name' => $driverData['last_name'],
                            'second_name' => $driverData['second_name'],
                            'phone' => $driverData['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'is_default' => true,
                                    'phone' => $driverData['phones'][0]['phone'],
                                ]
                            ],
                            'email' => $driverData['email'],
                            'comment' => $driverData['comment'],
                            'client' => [
                                'id' => $client->id,
                            ],
                        ]
                    ]
                ]
            );
    }

    public function test_update_driver_without_client(): void
    {
        $driver = Driver::factory()
            ->create();

        $driverData = [
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
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(DriverUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $driver->id,
                        'driver' => $driverData
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
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        DriverUpdateMutation::NAME => [
                            'id' => $driver->id,
                            'first_name' => $driverData['first_name'],
                            'last_name' => $driverData['last_name'],
                            'second_name' => $driverData['second_name'],
                            'phone' => $driverData['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'is_default' => true,
                                    'phone' => $driverData['phones'][0]['phone'],
                                ]
                            ],
                            'email' => $driverData['email'],
                            'comment' => $driverData['comment'],
                            'client' => null,
                        ]
                    ]
                ]
            );
    }

    public function test_try_update_driver_with_not_unique_email(): void
    {
        $driver = Driver::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(DriverUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $driver->id,
                        'driver' => [
                            'first_name' => $this->faker->firstName,
                            'last_name' => $this->faker->lastName,
                            'second_name' => $this->faker->lastName,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                ]
                            ],
                            'email' => Driver::factory()
                                ->create()->email,
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
}
