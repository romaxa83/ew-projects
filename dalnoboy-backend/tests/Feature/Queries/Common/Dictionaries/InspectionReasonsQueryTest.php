<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseInspectionReasonsQuery;
use App\Models\Dictionaries\InspectionReason;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InspectionReasonsQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $inspectionReasons;

    public function setUp(): void
    {
        parent::setUp();

        $this->inspectionReasons = InspectionReason::factory()
            ->count(15)
            ->create();
    }

    public function test_get_inspection_reasons_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseInspectionReasonsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'translate' => [
                                'title',
                                'language'
                            ],
                            'translates' => [
                                'title',
                                'language'
                            ],
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseInspectionReasonsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'translate' => [
                                        'title',
                                        'language'
                                    ],
                                    'translates' => [
                                        '*' => [
                                            'title',
                                            'language'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                InspectionReason::query()
                    ->count(),
                'data.' . BaseInspectionReasonsQuery::NAME . '.data'
            );
    }

    public function test_get_inspection_reasons_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->inspectionReasons[0]->active = false;
        $this->inspectionReasons[0]->save();

        $this->inspectionReasons[1]->active = false;
        $this->inspectionReasons[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionReasonsQuery::NAME)
                ->args(
                    [
                        'active' => false,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseInspectionReasonsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->inspectionReasons[1]->id,
                                ],
                                [
                                    'id' => $this->inspectionReasons[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseInspectionReasonsQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $this->loginAsUserWithRole();
        $this->inspectionReasons[0]->active = false;
        $this->inspectionReasons[0]->save();

        $this->inspectionReasons[1]->active = false;
        $this->inspectionReasons[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionReasonsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount(13, 'data.' . BaseInspectionReasonsQuery::NAME . '.data');
    }
}
