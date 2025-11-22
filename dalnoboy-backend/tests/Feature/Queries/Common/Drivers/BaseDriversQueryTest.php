<?php


namespace Tests\Feature\Queries\Common\Drivers;


use App\GraphQL\Queries\Common\Drivers\BaseDriversQuery;
use App\Models\Drivers\Driver;
use App\Models\Phones\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BaseDriversQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $drivers;

    public function setUp(): void
    {
        parent::setUp();

        $this->drivers = Driver::factory()
            ->count(13)
            ->create();
    }

    public function test_get_all_drivers_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();
        $this->getAllDriversList();
    }

    public function getAllDriversList(bool $backOffice = true): void
    {
        $this->{'postGraphQL' . ($backOffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseDriversQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'first_name',
                            'last_name',
                            'second_name',
                            'email',
                            'comment',
                            'phone',
                            'phones' => [
                                'is_default',
                                'phone'
                            ],
                            'client' => [
                                'id',
                                'name',
                                'contact_person',
                                'edrpou',
                                'manager' => [
                                    'id',
                                    'first_name',
                                    'last_name',
                                    'second_name',
                                    'city',
                                    'phone',
                                    'phones' => [
                                        'is_default',
                                        'phone'
                                    ],
                                    'created_at',
                                    'updated_at'
                                ],
                                'phone',
                                'phones' => [
                                    'is_default',
                                    'phone'
                                ],
                                'ban' => [
                                    'reason'
                                ],
                                'created_at',
                                'updated_at'
                            ],
                            'created_at',
                            'updated_at',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseDriversQuery::NAME => [
                            'data' => $this
                                ->drivers
                                ->map(
                                    fn(Driver $driver) => [
                                        'id' => $driver->id,
                                        'first_name' => $driver->first_name,
                                        'last_name' => $driver->last_name,
                                        'second_name' => $driver->second_name,
                                        'email' => $driver->email,
                                        'comment' => $driver->comment,
                                        'phone' => $driver->phone->phone,
                                        'phones' => $driver
                                            ->phones
                                            ->sortByDesc('is_default')
                                            ->map(
                                                fn(Phone $phone) => [
                                                    'is_default' => $phone->is_default,
                                                    'phone' => $phone->phone
                                                ]
                                            )
                                            ->toArray(),
                                        'client' => [
                                            'id' => $driver->client->id,
                                            'name' => $driver->client->name,
                                            'contact_person' => $driver->client->contact_person,
                                            'edrpou' => $driver->client->edrpou,
                                            'manager' => [
                                                'id' => $driver->client->manager->id,
                                                'first_name' => $driver->client->manager->first_name,
                                                'last_name' => $driver->client->manager->last_name,
                                                'second_name' => $driver->client->manager->second_name,
                                                'city' => $driver->client->manager->city,
                                                'phone' => $driver->client->manager->phone->phone,
                                                'phones' => $driver
                                                    ->client
                                                    ->manager
                                                    ->phones
                                                    ->sortByDesc('is_default')
                                                    ->map(
                                                        fn(Phone $phone) => [
                                                            'is_default' => $phone->is_default,
                                                            'phone' => $phone->phone
                                                        ]
                                                    )
                                                    ->toArray(),
                                                'created_at' => $driver->client->manager->created_at->getTimestamp(),
                                                'updated_at' => $driver->client->manager->updated_at->getTimestamp()
                                            ],
                                            'phone' => $driver->client->phone->phone,
                                            'phones' => $driver
                                                ->client
                                                ->phones
                                                ->sortByDesc('is_default')
                                                ->map(
                                                    fn(Phone $phone) => [
                                                        'is_default' => $phone->is_default,
                                                        'phone' => $phone->phone
                                                    ]
                                                )
                                                ->toArray(),
                                            'ban' => null,
                                            'created_at' => $driver->client->created_at->getTimestamp(),
                                            'updated_at' => $driver->client->updated_at->getTimestamp(),
                                        ],
                                        'created_at' => $driver->created_at->getTimestamp(),
                                        'updated_at' => $driver->updated_at->getTimestamp(),
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_get_all_drivers_list_by_user(): void
    {
        $this->loginAsUserWithRole();
        $this->getAllDriversList(false);
    }

    public function test_filter_by_full_name(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseDriversQuery::NAME)
                ->args(
                    [
                        'query' => $this->drivers[0]->last_name . ' ' . $this->drivers[0]->first_name
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseDriversQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->drivers[0]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseDriversQuery::NAME . '.data');
    }

    public function test_filter_by_email(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseDriversQuery::NAME)
                ->args(
                    [
                        'query' => $this->drivers[1]->email
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseDriversQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->drivers[1]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseDriversQuery::NAME . '.data');
    }

    public function test_filter_by_phone(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseDriversQuery::NAME)
                ->args(
                    [
                        'query' => $this->drivers[2]->phone->phone
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseDriversQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->drivers[2]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseDriversQuery::NAME . '.data');
    }

    public function test_filter_by_client_full_name(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseDriversQuery::NAME)
                ->args(
                    [
                        'query' => $this->drivers[3]->client->name
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseDriversQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->drivers[3]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseDriversQuery::NAME . '.data');
    }

    public function test_filter_by_client_phone(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseDriversQuery::NAME)
                ->args(
                    [
                        'query' => $this->drivers[4]->client->phone->phone
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseDriversQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->drivers[4]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseDriversQuery::NAME . '.data');
    }
}
