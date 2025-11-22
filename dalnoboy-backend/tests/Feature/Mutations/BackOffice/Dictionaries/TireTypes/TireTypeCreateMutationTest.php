<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes\TireTypeCreateMutation;
use App\Models\Dictionaries\TireType;
use App\Models\Dictionaries\TireTypeTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireTypeCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire_type(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $tireTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $tireTypeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireTypeCreateMutation::NAME)
                ->args(
                    [
                        'tire_type' => $tireTypeData
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
                        TireTypeCreateMutation::NAME => [
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
            ->json('data.' . TireTypeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireType::class,
            [
                'id' => $tireTypeId
            ]
        );

        $this->assertDatabaseHas(
            TireTypeTranslate::class,
            [
                'row_id' => $tireTypeId
            ]
        );
    }

    public function test_create_tire_type_only_with_default_language(): void
    {
        $title = $this->faker->text;
        $translates = [
            'language' => new EnumValue(defaultLanguage()->slug),
            'title' => $title,
        ];

        $tireTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $tireTypeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireTypeCreateMutation::NAME)
                ->args(
                    [
                        'tire_type' => $tireTypeData
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
                        TireTypeCreateMutation::NAME => [
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
            ->json('data.' . TireTypeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireType::class,
            [
                'id' => $tireTypeId
            ]
        );

        foreach (languages() as $language) {
            $this->assertDatabaseHas(
                TireTypeTranslate::class,
                [
                    'row_id' => $tireTypeId,
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

        $tireTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireTypeCreateMutation::NAME)
                ->args(
                    [
                        'tire_type' => $tireTypeData
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
