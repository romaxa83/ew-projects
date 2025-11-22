<?php

namespace Tests\Feature\Queries\BackOffice\Commercial;

use App\GraphQL\Queries\BackOffice\Commercial\CredentialsRequestsQuery;
use App\Models\Commercial\CredentialsRequest;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CredentialsRequestsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CredentialsRequestsQuery::NAME;

    public function test_get_list(): void
    {
        $this->loginAsSuperAdmin();

        CredentialsRequest::factory()
            ->times(10)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(self::QUERY)
                ->select(
                    [
                        'data' => [
                            'id',
                            'commercialProject' => [
                                'id'
                            ],
                            'company_phone',
                            'company_email',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount(10, 'data.' . self::QUERY . '.data');
    }
}