<?php


namespace Tests\Feature\Mutations\BackOffice\Clients;


use App\Enums\Clients\BanReasonsEnum;
use App\GraphQL\Mutations\BackOffice\Clients\ClientBanMutation;
use App\Models\Clients\Client;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ClientBanMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_ban_client(): void
    {
        $client = Client::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ClientBanMutation::NAME)
                ->args(
                    [
                        'id' => $client->id,
                        'ban' => [
                            'reason' => BanReasonsEnum::NON_PAYMENT()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'ban' => [
                            'reason',
                            'reason_description',
                            'show_in_inspection',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ClientBanMutation::NAME => [
                            'id' => $client->id,
                            'ban' => [
                                'reason' => BanReasonsEnum::NON_PAYMENT,
                                'reason_description' => BanReasonsEnum::NON_PAYMENT()->description,
                                'show_in_inspection' => false
                            ],
                        ]
                    ]
                ]
            );
    }

    public function test_unban_client(): void
    {
        $client = Client::factory()
            ->withBan()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ClientBanMutation::NAME)
                ->args(
                    [
                        'id' => $client->id,
                    ]
                )
                ->select(
                    [
                        'id',
                        'ban' => [
                            'reason',
                            'reason_description',
                            'show_in_inspection',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ClientBanMutation::NAME => [
                            'id' => $client->id,
                            'ban' => null,
                        ]
                    ]
                ]
            );
    }
}
