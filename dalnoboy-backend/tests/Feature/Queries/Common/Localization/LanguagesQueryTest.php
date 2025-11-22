<?php

namespace Tests\Feature\Queries\Common\Localization;

use App\GraphQL\Queries\Common\Localization\LanguagesQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Tests\TestCase;

class LanguagesQueryTest extends TestCase
{
    public function test_get_languages(): void
    {
        $this->postGraphQL(
            GraphQLQuery::query(LanguagesQuery::NAME)
                ->select(
                    [
                        'name',
                        'slug',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        LanguagesQuery::NAME => [
                            '*' => [
                                'name',
                                'slug'
                            ]
                        ]
                    ]
                ]
            );
    }
}
