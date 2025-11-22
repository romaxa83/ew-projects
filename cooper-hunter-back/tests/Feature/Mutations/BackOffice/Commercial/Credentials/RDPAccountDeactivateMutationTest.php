<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Credentials;

use App\GraphQL\Mutations\BackOffice\Commercial\Credentials\RDPAccountDeactivateMutation;
use App\Models\Commercial\RDPAccount;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RDPAccountDeactivateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = RDPAccountDeactivateMutation::NAME;

    public function test_deactivate_account(): void
    {
        $this->loginAsSuperAdmin();

        $account = RDPAccount::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'id' => $account->id,
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                    ]
                )
                ->make()
        )
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $account->id,
                            'active' => false,
                        ],
                    ],
                ]
            );
    }
}