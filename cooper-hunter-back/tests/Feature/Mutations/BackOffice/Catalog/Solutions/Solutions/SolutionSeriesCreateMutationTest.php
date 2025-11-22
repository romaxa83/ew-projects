<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Solutions\Solutions;

use App\GraphQL\Mutations\BackOffice\Catalog\Solutions\Series\SolutionSeriesCreateMutation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SolutionSeriesCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SolutionSeriesCreateMutation::NAME;

    public function test_create(): void
    {
        $this->loginAsSuperAdmin();

        $data = [
            'slug' => 'some-unique-slug',
            'translations' => [
                [
                    'language' => 'en',
                    'title' => 'en title'
                ],
                [
                    'language' => 'es',
                    'title' => 'es title'
                ]
            ],
        ];

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'input' => $data,
                ],
            )
            ->select(
                [
                    'id',
                    'slug',
                    'translations' => [
                        'language',
                        'title',
                    ],
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'slug',
                            'translations' => [
                                [
                                    'language',
                                    'title',
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }
}