<?php


namespace Tests\Feature\Mutations\FrontOffice\Clients;


use App\Enums\Utilities\MorphModelNameEnum;
use App\GraphQL\Mutations\FrontOffice\Clients\ClientCreateMutation;
use App\Models\Clients\Client;
use App\Models\Managers\Manager;
use App\Models\Phones\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class ClientCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private Manager $manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsUserWithRole();

        $this->manager = Manager::factory()
            ->create();
    }

    public function test_create_client_with_edrpou(): void
    {
        $client = [
            'name' => $this->faker->company,
            'contact_person' => $this->faker->firstName . ' ' . $this->faker->lastName,
            'manager_id' => $this->manager->id,
            'edrpou' => '32855961',
            'phones' => [
                [
                    'phone' => $this->faker->ukrainianPhone,
                    'is_default' => true
                ]
            ]
        ];

        $clientId = $this->postGraphQL(
            GraphQLQuery::mutation(ClientCreateMutation::NAME)
                ->args(
                    [
                        'client' => $client
                    ]
                )
                ->select(
                    [
                        'id',
                        'name',
                        'contact_person',
                        'manager' => [
                            'id',
                        ],
                        'edrpou',
                        'inn',
                        'ban' => [
                            'reason',
                            'reason_description',
                            'show_in_inspection',
                        ],
                        'phone',
                        'phones' => [
                            'phone',
                            'is_default',
                        ],
                        'is_moderated',
                        'active',
                        'created_at',
                        'updated_at',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        ClientCreateMutation::NAME => [
                            'id',
                            'name',
                            'contact_person',
                            'manager' => [
                                'id',
                            ],
                            'edrpou',
                            'inn',
                            'ban',
                            'phone',
                            'phones' => [
                                '*' => [
                                    'phone',
                                    'is_default',
                                ]
                            ],
                            'is_moderated',
                            'active',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        ClientCreateMutation::NAME => [
                            'name' => $client['name'],
                            'contact_person' => $client['contact_person'],
                            'manager' => [
                                'id' => $this->manager->id,
                            ],
                            'edrpou' => $client['edrpou'],
                            'inn' => null,
                            'ban' => null,
                            'phone' => $client['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'phone' => $client['phones'][0]['phone'],
                                    'is_default' => true,
                                ]
                            ],
                            'is_moderated' => false,
                            'active' => true,
                        ]
                    ]
                ]
            )
            ->json('data.' . ClientCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Client::class,
            [
                'id' => $clientId
            ]
        );

        $this->assertDatabaseHas(
            Phone::class,
            [
                'owner_type' => MorphModelNameEnum::client()->key,
                'owner_id' => $clientId
            ]
        );
    }

    public function test_create_client_with_inn(): void
    {
        $client = [
            'name' => $this->faker->company,
            'contact_person' => $this->faker->firstName . ' ' . $this->faker->lastName,
            'manager_id' => $this->manager->id,
            'inn' => '2245134075',
            'phones' => [
                [
                    'phone' => $this->faker->ukrainianPhone,
                    'is_default' => true
                ]
            ]
        ];

        $clientId = $this->postGraphQL(
            GraphQLQuery::mutation(ClientCreateMutation::NAME)
                ->args(
                    [
                        'client' => $client
                    ]
                )
                ->select(
                    [
                        'id',
                        'name',
                        'contact_person',
                        'manager' => [
                            'id',
                        ],
                        'edrpou',
                        'inn',
                        'ban' => [
                            'reason',
                            'reason_description',
                            'show_in_inspection',
                        ],
                        'phone',
                        'phones' => [
                            'phone',
                            'is_default',
                        ],
                        'created_at',
                        'updated_at',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        ClientCreateMutation::NAME => [
                            'id',
                            'name',
                            'contact_person',
                            'manager' => [
                                'id',
                            ],
                            'edrpou',
                            'inn',
                            'ban',
                            'phone',
                            'phones' => [
                                '*' => [
                                    'phone',
                                    'is_default',
                                ]
                            ],
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        ClientCreateMutation::NAME => [
                            'name' => $client['name'],
                            'contact_person' => $client['contact_person'],
                            'manager' => [
                                'id' => $this->manager->id,
                            ],
                            'edrpou' => null,
                            'inn' => $client['inn'],
                            'ban' => null,
                            'phone' => $client['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'phone' => $client['phones'][0]['phone'],
                                    'is_default' => true,
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . ClientCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Client::class,
            [
                'id' => $clientId
            ]
        );

        $this->assertDatabaseHas(
            Phone::class,
            [
                'owner_type' => MorphModelNameEnum::client()->key,
                'owner_id' => $clientId
            ]
        );
    }

    public function test_try_to_create_client_with_not_uniq_edrpou(): void
    {
        $client = Client::factory(['edrpou' => '32855961'])
            ->create();

        $this->postGraphQL(
            GraphQLQuery::mutation(ClientCreateMutation::NAME)
                ->args(
                    [
                        'client' => [
                            'name' => $this->faker->company,
                            'contact_person' => $this->faker->firstName . ' ' . $this->faker->lastName,
                            'manager_id' => $this->manager->id,
                            'edrpou' => $client->edrpou,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                    'is_default' => true
                                ]
                            ]
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
                            'message' => trans('validation.custom.clients.uniq')
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_create_client_without_edrpou_inn(): void
    {
        $this->postGraphQL(
            GraphQLQuery::mutation(ClientCreateMutation::NAME)
                ->args(
                    [
                        'client' => [
                            'name' => $this->faker->company,
                            'contact_person' => $this->faker->firstName . ' ' . $this->faker->lastName,
                            'manager_id' => $this->manager->id,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                    'is_default' => true
                                ]
                            ]
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
                            'message' => 'validation',
                            'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY
                        ]
                    ]
                ]
            )
            ->assertJsonStructure(
                [
                    'errors' => [
                        [
                            'extensions' => [
                                'validation' => [
                                    'client.edrpou',
                                    'client.inn'
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_create_client_without_manager(): void
    {
        $this->postGraphQL(
            GraphQLQuery::mutation(ClientCreateMutation::NAME)
                ->args(
                    [
                        'client' => [
                            'name' => $this->faker->company,
                            'contact_person' => $this->faker->firstName . ' ' . $this->faker->lastName,
                            'edrpou' => '32855961',
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                    'is_default' => true
                                ]
                            ]
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'manager' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        ClientCreateMutation::NAME => [
                            'id',
                            'manager'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        ClientCreateMutation::NAME => [
                            'manager' => null
                        ]
                    ]
                ]
            );
    }
}
