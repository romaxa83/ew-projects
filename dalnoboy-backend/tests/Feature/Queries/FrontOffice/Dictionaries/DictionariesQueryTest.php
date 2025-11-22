<?php

namespace Tests\Feature\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\FrontOffice\Dictionaries\DictionariesQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DictionariesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_dictionaries_list(): void
    {
        $this->loginAsUserWithRole();

        $this->postGraphQl(
            GraphQLQuery::query(DictionariesQuery::NAME)
                ->select(
                    [
                        'name',
                        'count',
                        'updated_at',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        DictionariesQuery::NAME => [
                            '*' => [
                                'name',
                                'count',
                                'updated_at',
                            ]
                        ]
                    ]
                ]
            );
    }
}
