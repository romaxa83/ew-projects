<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\InspectionReasons;

use App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons\InspectionReasonCreateMutation;
use App\Models\Dictionaries\InspectionReason;
use App\Models\Dictionaries\InspectionReasonTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InspectionReasonCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_inspection_reason(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $inspectionReasonData = [
            'active' => true,
            'need_description' => true,
            'translations' => $translates,
        ];

        $inspectionReasonId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionReasonCreateMutation::NAME)
                ->args(
                    [
                        'inspection_reason' => $inspectionReasonData
                    ]
                )
                ->select(
                    [
                        'id',
                        'need_description',
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
                        InspectionReasonCreateMutation::NAME => [
                            'id',
                            'need_description',
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
            ->json('data.' . InspectionReasonCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            InspectionReason::class,
            [
                'id' => $inspectionReasonId,
                'need_description' => true,
            ]
        );

        $this->assertDatabaseHas(
            InspectionReasonTranslate::class,
            [
                'row_id' => $inspectionReasonId
            ]
        );
    }

    public function test_create_inspection_reason_only_with_default_language(): void
    {
        $translates = [
            'language' => new EnumValue(defaultLanguage()->slug),
            'title' => $this->faker->text,
        ];

        $inspectionReasonData = [
            'active' => true,
            'need_description' => true,
            'translations' => $translates,
        ];

        $inspectionReasonId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionReasonCreateMutation::NAME)
                ->args(
                    [
                        'inspection_reason' => $inspectionReasonData
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
                        InspectionReasonCreateMutation::NAME => [
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
            ->json('data.' . InspectionReasonCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            InspectionReason::class,
            [
                'id' => $inspectionReasonId
            ]
        );

        $this->assertDatabaseHas(
            InspectionReasonTranslate::class,
            [
                'row_id' => $inspectionReasonId
            ]
        );
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

        $inspectionReasonData = [
            'active' => true,
            'need_description' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionReasonCreateMutation::NAME)
                ->args(
                    [
                        'inspection_reason' => $inspectionReasonData
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
