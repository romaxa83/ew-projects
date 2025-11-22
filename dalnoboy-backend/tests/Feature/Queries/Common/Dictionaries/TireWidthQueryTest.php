<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireWidthQuery;
use App\Models\Dictionaries\TireWidth;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireWidthQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tireWidths;

    public function setUp(): void
    {
        parent::setUp();

        $this->tireWidths = TireWidth::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tire_width_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTireWidthQuery::NAME)
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
                        BaseTireWidthQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'value',
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                TireWidth::query()
                    ->count(),
                'data.' . BaseTireWidthQuery::NAME . '.data'
            );
    }

    public function test_get_tire_width_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireWidths[0]->active = false;
        $this->tireWidths[0]->value = 20;
        $this->tireWidths[0]->save();
        $this->tireWidths[1]->active = false;
        $this->tireWidths[1]->value = 10;
        $this->tireWidths[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireWidthQuery::NAME)
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
                        BaseTireWidthQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireWidths[1]->id,
                                ],
                                [
                                    'id' => $this->tireWidths[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireWidthQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = TireWidth::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->tireWidths[0]->active = false;
        $this->tireWidths[0]->save();
        $this->tireWidths[1]->active = false;
        $this->tireWidths[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTireWidthQuery::NAME)
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
            ->assertJsonCount($startCount - 2, 'data.' . BaseTireWidthQuery::NAME . '.data');
    }
}
