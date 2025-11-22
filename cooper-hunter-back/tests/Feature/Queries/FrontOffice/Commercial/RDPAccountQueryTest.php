<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\FrontOffice\Commercial\RDPAccountQuery;
use App\Models\Commercial\RDPAccount;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RDPAccountQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = RDPAccountQuery::NAME;

    public function test_get_rdp_account(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $rdp = RDPAccount::factory()
            ->forTechnician($technician)
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(self::QUERY)
                ->select(
                    [
                        'id',
                        'member' => [
                            'name',
                            'email',
                        ],
                        'active',
                        'login',
                        'password',
                        'start_date',
                        'end_date',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $rdp->id,
                            'login' => $rdp->login,
                            'password' => $rdp->password,
                        ],
                    ],
                ],
            );
    }
}