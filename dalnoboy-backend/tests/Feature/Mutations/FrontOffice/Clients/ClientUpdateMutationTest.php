<?php


namespace Tests\Feature\Mutations\FrontOffice\Clients;


use App\GraphQL\Mutations\FrontOffice\Clients\ClientUpdateMutation;
use App\Models\Clients\Client;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsUserWithRole();
    }

    public function test_update_client(): void
    {
        $client = Client::factory()
            ->create();

        $response = $this->postGraphQL(
            GraphQLQuery::mutation(ClientUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $client->id,
                        'client' => [
                            'name' => $this->faker->company,
                            'contact_person' => $this->faker->firstName . ' ' . $this->faker->lastName,
                            'manager_id' => $client->manager_id,
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
                        'name',
                        'contact_person',
                        'manager' => [
                            'id',
                        ],
                        'edrpou',
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
                        'updated_at',
                        'created_at',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        ClientUpdateMutation::NAME => [
                            'id',
                            'name',
                            'contact_person',
                            'manager' => [
                                'id',
                            ],
                            'edrpou',
                            'ban',
                            'phone',
                            'phones' => [
                                '*' => [
                                    'phone',
                                    'is_default',
                                ]
                            ],
                            'is_moderated',
                            'updated_at',
                            'created_at'
                        ]
                    ]
                ]
            );

        $client->refresh();

        $response
            ->assertJson(
                [
                    'data' => [
                        ClientUpdateMutation::NAME => [
                            'id' => $client->id,
                            'name' => $client->name,
                            'contact_person' => $client->contact_person,
                            'manager' => [
                                'id' => $client->manager_id,
                            ],
                            'edrpou' => '32855961',
                            'ban' => null,
                            'phone' => $client->phone->phone,
                            'phones' => [
                                [
                                    'phone' => $client->phone->phone,
                                    'is_default' => true,
                                ]
                            ],
                            'is_moderated' => false,
                            'updated_at' => $client->updated_at->getTimestamp(),
                            'created_at' => $client->created_at->getTimestamp(),
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_update_client_with_not_uniq_edrpou(): void
    {
        $client = Client::factory()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::mutation(ClientUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $client->id,
                        'client' => [
                            'name' => $this->faker->company,
                            'contact_person' => $this->faker->firstName . ' ' . $this->faker->lastName,
                            'manager_id' => $client->manager->id,
                            'edrpou' => Client::factory(['edrpou' => '32855961'])
                                ->create()->edrpou,
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
}
