<?php


namespace Tests\Feature\Mutations\BackOffice\Clients;


use App\GraphQL\Mutations\BackOffice\Clients\ClientDeleteMutation;
use App\Models\Clients\Client;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ClientDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_client(): void
    {
        $client = Client::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ClientDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $client->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ClientDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Client::class,
            [
                'id' => $client->id
            ]
        );
    }
}
