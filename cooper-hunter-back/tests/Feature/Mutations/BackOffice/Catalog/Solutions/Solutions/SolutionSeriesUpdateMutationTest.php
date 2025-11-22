<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Solutions\Solutions;

use App\GraphQL\Mutations\BackOffice\Catalog\Solutions\Series\SolutionSeriesUpdateMutation;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SolutionSeriesUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SolutionSeriesUpdateMutation::NAME;

    public function test_update(): void
    {
        $this->loginAsSuperAdmin();

        $data = [
            'slug' => 'some-unique-slug',
            'translations' => [
                [
                    'language' => 'en',
                    'title' => 'en title',
                    'description' => 'en description',
                ],
                [
                    'language' => 'es',
                    'title' => 'es title',
                ]
            ],
        ];

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'id' => SolutionSeries::factory()->create()->id,
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
                        'description',
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