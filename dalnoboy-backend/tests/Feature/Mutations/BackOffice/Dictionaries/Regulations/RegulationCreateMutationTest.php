<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Regulations;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations\RegulationCreateMutation;
use App\Models\Dictionaries\Regulation;
use App\Models\Dictionaries\RegulationTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegulationCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_regulation(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $regulationData = [
            'active' => true,
            'days' => 10,
            'translations' => $translates,
        ];

        $regulationId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RegulationCreateMutation::NAME)
                ->args(
                    [
                        'regulation' => $regulationData
                    ]
                )
                ->select(
                    [
                        'id',
                        'days',
                        'distance',
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
                        RegulationCreateMutation::NAME => [
                            'id',
                            'days',
                            'distance',
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
            ->json('data.' . RegulationCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Regulation::class,
            [
                'id' => $regulationId
            ]
        );

        $this->assertDatabaseHas(
            RegulationTranslate::class,
            [
                'row_id' => $regulationId
            ]
        );
    }

    public function test_create_regulation_only_with_default_language(): void
    {
        $title = $this->faker->text;
        $translates = [
            'language' => new EnumValue(defaultLanguage()->slug),
            'title' => $title,
        ];

        $regulationData = [
            'active' => true,
            'distance' => 40,
            'translations' => $translates,
        ];

        $regulationId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RegulationCreateMutation::NAME)
                ->args(
                    [
                        'regulation' => $regulationData
                    ]
                )
                ->select(
                    [
                        'id',
                        'days',
                        'distance',
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
                        RegulationCreateMutation::NAME => [
                            'id',
                            'days',
                            'distance',
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
            ->json('data.' . RegulationCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Regulation::class,
            [
                'id' => $regulationId
            ]
        );

        foreach (languages() as $language) {
            $this->assertDatabaseHas(
                RegulationTranslate::class,
                [
                    'row_id' => $regulationId,
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

        $regulationData = [
            'active' => true,
            'distance' => 40,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RegulationCreateMutation::NAME)
                ->args(
                    [
                        'regulation' => $regulationData
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

    public function test_with_empty_days_and_distance(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $regulationData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RegulationCreateMutation::NAME)
                ->args(
                    [
                        'regulation' => $regulationData
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
