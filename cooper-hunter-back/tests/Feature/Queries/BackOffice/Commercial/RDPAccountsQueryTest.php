<?php

namespace Tests\Feature\Queries\BackOffice\Commercial;

use App\GraphQL\Queries\BackOffice\Commercial\RDPAccountsQuery;
use App\Models\Commercial\RDPAccount;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RDPAccountsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = RDPAccountsQuery::NAME;

    public function test_get_list(): void
    {
        $this->loginAsSuperAdmin();

        RDPAccount::factory()
            ->times(10)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(self::QUERY)
                ->select(
                    [
                        'data' => $select = [
                            'id',
                            'member' => [
                                'name',
                            ],
                            'login',
                            'password',
                            'active',
                            'start_date',
                            'end_date',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                $select
                            ],
                        ],
                    ],
                ]
            );
    }
}