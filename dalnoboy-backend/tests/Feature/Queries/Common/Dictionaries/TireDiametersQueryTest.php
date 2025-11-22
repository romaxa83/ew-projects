<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireDiametersQuery;
use App\Models\Dictionaries\TireDiameter;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireDiametersQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tireDiameters;

    public function setUp(): void
    {
        parent::setUp();

        $this->tireDiameters = TireDiameter::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tire_diameters_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTireDiametersQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                            'value',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseTireDiametersQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'value',
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                TireDiameter::query()
                    ->count(),
                'data.' . BaseTireDiametersQuery::NAME . '.data'
            );
    }

    public function test_get_tire_diameters_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireDiameters[0]->active = false;
        $this->tireDiameters[0]->value = '20';
        $this->tireDiameters[0]->save();
        $this->tireDiameters[1]->active = false;
        $this->tireDiameters[1]->value = '10';
        $this->tireDiameters[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireDiametersQuery::NAME)
                ->args(
                    [
                        'active' => false,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseTireDiametersQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireDiameters[1]->id,
                                ],
                                [
                                    'id' => $this->tireDiameters[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireDiametersQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = TireDiameter::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->tireDiameters[0]->active = false;
        $this->tireDiameters[0]->save();
        $this->tireDiameters[1]->active = false;
        $this->tireDiameters[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTireDiametersQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount($startCount - 2, 'data.' . BaseTireDiametersQuery::NAME . '.data');
    }
}
