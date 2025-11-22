<?php

namespace Tests\Feature\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\FrontOffice\Dictionaries\TireChangesReasonsQuery;
use App\Models\Dictionaries\TireChangesReason;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireChangesReasonsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_reasons_list(): void
    {
        $this->loginAsUserWithRole();

        $this->postGraphQl(
            GraphQLQuery::query(TireChangesReasonsQuery::NAME)
                ->select(
                    [
                        'id',
                        'need_description',
                        'translate' => [
                            'title',
                            'language'
                        ],
                        'translates' => [
                            'title',
                            'language'
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        TireChangesReasonsQuery::NAME => [
                            '*' => [
                                'id',
                                'need_description',
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
            )
            ->assertJsonCount(
                TireChangesReason::query()
                    ->count(),
                'data.' . TireChangesReasonsQuery::NAME
            );
    }
}
