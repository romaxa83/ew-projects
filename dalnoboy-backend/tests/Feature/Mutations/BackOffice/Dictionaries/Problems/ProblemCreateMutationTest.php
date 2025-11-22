<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Problems;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Problems\ProblemCreateMutation;
use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\ProblemTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProblemCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_problem(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $problemData = [
            'active' => true,
            'translations' => $translates,
        ];

        $problemId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ProblemCreateMutation::NAME)
                ->args(
                    [
                        'problem' => $problemData
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
                        ProblemCreateMutation::NAME => [
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
            ->json('data.' . ProblemCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Problem::class,
            [
                'id' => $problemId
            ]
        );

        $this->assertDatabaseHas(
            ProblemTranslate::class,
            [
                'row_id' => $problemId
            ]
        );
    }

    public function test_create_problem_only_with_default_language(): void
    {
        $title = $this->faker->text;
        $translates = [
            'language' => new EnumValue(defaultLanguage()->slug),
            'title' => $title,
        ];

        $problemData = [
            'active' => true,
            'translations' => $translates,
        ];

        $problemId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ProblemCreateMutation::NAME)
                ->args(
                    [
                        'problem' => $problemData
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
                        ProblemCreateMutation::NAME => [
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
            ->json('data.' . ProblemCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Problem::class,
            [
                'id' => $problemId
            ]
        );

        foreach (languages() as $language) {
            $this->assertDatabaseHas(
                ProblemTranslate::class,
                [
                    'row_id' => $problemId,
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

        $problemData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ProblemCreateMutation::NAME)
                ->args(
                    [
                        'problem' => $problemData
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
