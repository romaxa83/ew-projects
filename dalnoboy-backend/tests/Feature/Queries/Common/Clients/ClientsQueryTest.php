<?php


namespace Tests\Feature\Queries\Common\Clients;


use App\GraphQL\Queries\Common\Clients\BaseClientsQuery;
use App\Models\Clients\Client;
use App\Models\Phones\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ClientsQueryTest extends TestCase
{
    use DatabaseTransactions;

    /**@var Client[] $clients */
    private iterable $clients;

    public function setUp(): void
    {
        parent::setUp();

        $this->clients = Client::factory()
            ->count(6)
            ->create()
            ->merge(
                Client::factory()
                    ->withBan()
                    ->count(5)
                    ->create()
            );
    }

    public function test_show_all_clients_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->getClientsList();
    }

    private function getClientsList(bool $backOffice = true)
    {
        $this->{'postGraphQL' . ($backOffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseClientsQuery::NAME)
                ->select(
                    [
                        'data' => [
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
                                    'phone',
                                    'is_default'
                                ],
                                'created_at',
                                'updated_at',
                            ],
                            'ban' => [
                                'reason',
                                'reason_description',
                                'show_in_inspection',
                            ],
                            'phone',
                            'phones' => [
                                'phone',
                                'is_default'
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
                        BaseClientsQuery::NAME => [
                            'data' => $this
                                ->clients
                                ->sortBy('name')
                                ->map(
                                    fn(Client $client) => [
                                        'id' => $client->id,
                                        'name' => $client->name,
                                        'contact_person' => $client->contact_person,
                                        'edrpou' => $client->edrpou,
                                        'manager' => [
                                            'id' => $client->manager->id,
                                            'first_name' => $client->manager->first_name,
                                            'last_name' => $client->manager->last_name,
                                            'second_name' => $client->manager->second_name,
                                            'city' => $client->manager->city,
                                            'phone' => $client->manager->phone->phone,
                                            'phones' => $client
                                                ->manager
                                                ->phones
                                                ->sortByDesc('is_default')
                                                ->sortByDesc('phone')
                                                ->map(
                                                    fn(Phone $phone) => [
                                                        'phone' => $phone->phone,
                                                        'is_default' => $phone->is_default
                                                    ]
                                                )
                                                ->toArray(),
                                            'created_at' => $client->manager->created_at->getTimestamp(),
                                            'updated_at' => $client->manager->updated_at->getTimestamp(),
                                        ],
                                        'ban' => !$client->ban_reason ? null : [
                                            'reason' => $client->ban_reason->value,
                                            'reason_description' => $client->ban_reason->description,
                                            'show_in_inspection' => $client->show_ban_in_inspection,
                                        ],
                                        'phone' => $client->phone->phone,
                                        'phones' => $client
                                            ->phones
                                            ->sortByDesc('is_default')
                                            ->sortByDesc('phone')
                                            ->map(
                                                fn(Phone $phone) => [
                                                    'phone' => $phone->phone,
                                                    'is_default' => $phone->is_default
                                                ]
                                            )
                                            ->toArray(),
                                        'created_at' => $client->created_at->getTimestamp(),
                                        'updated_at' => $client->updated_at->getTimestamp(),
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_show_all_clients_by_inspector(): void
    {
        $this->loginAsUserWithRole();

        $this->getClientsList(false);
    }

    public function test_filter_by_name(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseClientsQuery::NAME)
                ->args(
                    [
                        'query' => $this->clients[0]->name
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
                        BaseClientsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->clients[0]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseClientsQuery::NAME . '.data');
    }

    public function test_filter_by_contact_person(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseClientsQuery::NAME)
                ->args(
                    [
                        'query' => $this->clients[1]->contact_person
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
                        BaseClientsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->clients[1]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseClientsQuery::NAME . '.data');
    }

    public function test_filter_by_phone(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseClientsQuery::NAME)
                ->args(
                    [
                        'query' => $this->clients[2]->phone->phone
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
                        BaseClientsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->clients[2]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseClientsQuery::NAME . '.data');
    }

    public function test_filter_by_edrpou(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseClientsQuery::NAME)
                ->args(
                    [
                        'query' => $this->clients[3]->edrpou
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
                        BaseClientsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->clients[3]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseClientsQuery::NAME . '.data');
    }

    public function test_filter_by_manager_phone(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseClientsQuery::NAME)
                ->args(
                    [
                        'query' => $this->clients[4]->manager->phone->phone
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
                        BaseClientsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->clients[4]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseClientsQuery::NAME . '.data');
    }

    public function test_filter_by_manager_full_name(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseClientsQuery::NAME)
                ->args(
                    [
                        'query' => $this->clients[5]->manager->last_name . ' ' . $this->clients[5]->manager->first_name
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
                        BaseClientsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->clients[5]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseClientsQuery::NAME . '.data');
    }

    public function test_sort_by_name_desc(): void
    {
        $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseClientsQuery::NAME)
                ->args(
                    [
                        'sort' => [
                            'name-desc'
                        ]
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
                        BaseClientsQuery::NAME => [
                            'data' => $this
                                ->clients
                                ->sortByDesc('name')
                                ->values()
                                ->map(
                                    fn(Client $client) => [
                                        'id' => $client->id
                                    ]
                                )
                                ->toArray()
                        ]
                    ]
                ]
            );
    }
}
