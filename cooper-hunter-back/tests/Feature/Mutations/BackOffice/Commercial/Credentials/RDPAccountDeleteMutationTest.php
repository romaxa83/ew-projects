<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Credentials;

use App\GraphQL\Mutations\BackOffice\Commercial\Credentials\RDPAccountDeleteMutation;
use App\Models\Commercial\RDPAccount;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RDPAccountDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = RDPAccountDeleteMutation::NAME;

    public function test_delete_account(): void
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
                ->make()
        )
            ->assertOk();

        $this->assertModelMissing($account);
    }
}