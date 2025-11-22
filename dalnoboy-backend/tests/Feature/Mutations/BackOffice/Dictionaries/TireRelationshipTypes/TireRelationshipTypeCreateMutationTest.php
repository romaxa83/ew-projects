<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireRelationshipTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes\TireRelationshipTypeCreateMutation;
use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireRelationshipTypeTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireRelationshipTypeCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire_relationship_type(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $tireRelationshipTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $tireRelationshipTypeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireRelationshipTypeCreateMutation::NAME)
                ->args(
                    [
                        'tire_relationship_type' => $tireRelationshipTypeData
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
                        TireRelationshipTypeCreateMutation::NAME => [
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
            ->json('data.' . TireRelationshipTypeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireRelationshipType::class,
            [
                'id' => $tireRelationshipTypeId
            ]
        );

        $this->assertDatabaseHas(
            TireRelationshipTypeTranslate::class,
            [
                'row_id' => $tireRelationshipTypeId
            ]
        );
    }

    public function test_create_tire_relationship_type_only_with_default_language(): void
    {
        $title = $this->faker->text;
        $translates = [
            'language' => new EnumValue(defaultLanguage()->slug),
            'title' => $title,
        ];

        $tireRelationshipTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $tireRelationshipTypeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireRelationshipTypeCreateMutation::NAME)
                ->args(
                    [
                        'tire_relationship_type' => $tireRelationshipTypeData
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
                        TireRelationshipTypeCreateMutation::NAME => [
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
            ->json('data.' . TireRelationshipTypeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireRelationshipType::class,
            [
                'id' => $tireRelationshipTypeId
            ]
        );

        foreach (languages() as $language) {
            $this->assertDatabaseHas(
                TireRelationshipTypeTranslate::class,
                [
                    'row_id' => $tireRelationshipTypeId,
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

        $tireRelationshipTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireRelationshipTypeCreateMutation::NAME)
                ->args(
                    [
                        'tire_relationship_type' => $tireRelationshipTypeData
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
