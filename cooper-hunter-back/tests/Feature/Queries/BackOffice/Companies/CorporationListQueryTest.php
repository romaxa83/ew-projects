<?php

namespace Tests\Feature\Queries\BackOffice\Companies;

use App\GraphQL\Queries\BackOffice\Companies\CorporationListQuery;
use App\Models\Companies\Corporation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CorporationListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CorporationListQuery::NAME;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list(): void
    {
        $this->loginAsSuperAdmin();

        Corporation::factory()->times(25)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        ['id', 'name']
                    ],
                ]
            ])
            ->assertJsonCount(25, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function list_empty(): void
    {
        $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJsonCount(0, 'data.'.self::MUTATION)
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    id
                    name
                }
            }',
            self::MUTATION
        );
    }
}
