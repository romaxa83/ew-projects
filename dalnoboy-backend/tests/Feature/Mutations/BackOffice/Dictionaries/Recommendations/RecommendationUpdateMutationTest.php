<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Recommendations;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations\RecommendationUpdateMutation;
use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\Recommendation;
use App\Models\Dictionaries\RecommendationTranslate;
use App\Models\Dictionaries\Regulation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RecommendationUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_recommendation(): void
    {
        $recommendation = Recommendation::factory()->create();
        $problem = Problem::factory()->create();
        $recommendation->problems()->attach($problem);
        $regulation = Regulation::factory()->create();
        $recommendation->regulations()->attach($regulation);

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

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RecommendationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $recommendation->id,
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
            ->assertOk();

        $this->assertDatabaseMissing(
            'problem_recommendation',
            [
                'problem_id' => $problem->getKey(),
                'recommendation_id' => $recommendation->getKey(),
            ]
        );

        foreach ($translates as $translate) {
            $this->assertDatabaseHas(
                RecommendationTranslate::class,
                [
                    'row_id' => $recommendation->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }

        $this->assertDatabaseHas(
            'problem_recommendation',
            [
                'problem_id' => $problem1->getKey(),
                'recommendation_id' => $recommendation->getKey(),
            ]
        );

        $this->assertDatabaseHas(
            'problem_recommendation',
            [
                'problem_id' => $problem2->getKey(),
                'recommendation_id' => $recommendation->getKey(),
            ]
        );

        $this->assertDatabaseMissing(
            'recommendation_regulation',
            [
                'regulation_id' => $regulation->getKey(),
                'recommendation_id' => $recommendation->getKey(),
            ]
        );

        $this->assertDatabaseHas(
            'recommendation_regulation',
            [
                'regulation_id' => $regulation1->getKey(),
                'recommendation_id' => $recommendation->getKey(),
            ]
        );

        $this->assertDatabaseHas(
            'recommendation_regulation',
            [
                'regulation_id' => $regulation2->getKey(),
                'recommendation_id' => $recommendation->getKey(),
            ]
        );
    }

    public function test_update_recommendation_only_with_default_language(): void
    {
        $recommendation = Recommendation::factory()->create();

        $translates = [];
        foreach (languages() as $language) {
            if (!$language->default) {
                continue;
            }
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $recommendationData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RecommendationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $recommendation->id,
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
            ->assertOk();

        foreach ($translates as $translate) {
            $this->assertDatabaseHas(
                RecommendationTranslate::class,
                [
                    'row_id' => $recommendation->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }
    public function test_empty_default_language(): void
    {
        $recommendation = Recommendation::factory()->create();

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

        $recommendationData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RecommendationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $recommendation->id,
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
