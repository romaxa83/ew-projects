<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Recommendations;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations\RecommendationCreateMutation;
use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\Recommendation;
use App\Models\Dictionaries\RecommendationTranslate;
use App\Models\Dictionaries\Regulation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RecommendationCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_recommendation(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $problem1 = Problem::factory()->create();
        $problem2 = Problem::factory()->create();

        $regulation1 = Regulation::factory()->create();
        $regulation2 = Regulation::factory()->create();

        $recommendationData = [
            'active' => true,
            'problems' => [$problem1->getKey(), $problem2->getKey()],
            'regulations' => [$regulation1->getKey(), $regulation2->getKey()],
            'translations' => $translates,
        ];

        $recommendationId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RecommendationCreateMutation::NAME)
                ->args(
                    [
                        'recommendation' => $recommendationData
                    ]
                )
                ->select(
                    [
                        'id',
                        'problems' => [
                            'id',
                        ],
                        'regulations' => [
                            'id',
                        ],
                        'translate' => [
                            'language',
                            'title',
                        ],
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        RecommendationCreateMutation::NAME => [
                            'id',
                            'problems' => [
                                '*' => [
                                    'id',
                                ]
                            ],
                            'regulations' => [
                                '*' => [
                                    'id',
                                ]
                            ],
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
            )
            ->json('data.' . RecommendationCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Recommendation::class,
            [
                'id' => $recommendationId,
            ]
        );

        $this->assertDatabaseHas(
            RecommendationTranslate::class,
            [
                'row_id' => $recommendationId,
            ]
        );

        $this->assertDatabaseHas(
            'problem_recommendation',
            [
                'problem_id' => $problem1->getKey(),
                'recommendation_id' => $recommendationId,
            ]
        );

        $this->assertDatabaseHas(
            'problem_recommendation',
            [
                'problem_id' => $problem2->getKey(),
                'recommendation_id' => $recommendationId,
            ]
        );

        $this->assertDatabaseHas(
            'recommendation_regulation',
            [
                'regulation_id' => $regulation1->getKey(),
                'recommendation_id' => $recommendationId,
            ]
        );

        $this->assertDatabaseHas(
            'recommendation_regulation',
            [
                'regulation_id' => $regulation2->getKey(),
                'recommendation_id' => $recommendationId,
            ]
        );
    }

    public function test_create_recommendation_only_with_default_language(): void
    {
        $title = $this->faker->text;
        $translates = [
            'language' => new EnumValue(defaultLanguage()->slug),
            'title' => $title,
        ];

        $problem1 = Problem::factory()->create();
        $problem2 = Problem::factory()->create();

        $regulation1 = Regulation::factory()->create();
        $regulation2 = Regulation::factory()->create();

        $recommendationData = [
            'active' => true,
            'problems' => [$problem1->getKey(), $problem2->getKey()],
            'regulations' => [$regulation1->getKey(), $regulation2->getKey()],
            'translations' => $translates,
        ];

        $recommendationId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RecommendationCreateMutation::NAME)
                ->args(
                    [
                        'recommendation' => $recommendationData
                    ]
                )
                ->select(
                    [
                        'id',
                        'translate' => [
                            'language',
                            'title',
                        ],
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        RecommendationCreateMutation::NAME => [
                            'id',
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
            )
            ->json('data.' . RecommendationCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Recommendation::class,
            [
                'id' => $recommendationId
            ]
        );

        foreach (languages() as $language) {
            $this->assertDatabaseHas(
                RecommendationTranslate::class,
                [
                    'row_id' => $recommendationId,
                    'language' => $language->slug,
                    'title' => $title,
                ]
            );
        }
    }

    public function test_empty_default_language(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            if ($language->default) {
                continue;
            }
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $problem1 = Problem::factory()->create();
        $problem2 = Problem::factory()->create();

        $regulation1 = Regulation::factory()->create();
        $regulation2 = Regulation::factory()->create();

        $recommendationData = [
            'active' => true,
            'problems' => [$problem1->getKey(), $problem2->getKey()],
            'regulations' => [$regulation1->getKey(), $regulation2->getKey()],
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RecommendationCreateMutation::NAME)
                ->args(
                    [
                        'recommendation' => $recommendationData
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'validation'
                        ]
                    ]
                ]
            );
    }
}
