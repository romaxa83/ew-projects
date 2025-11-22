<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\InspectionReasons;

use App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons\InspectionReasonUpdateMutation;
use App\Models\Dictionaries\InspectionReason;
use App\Models\Dictionaries\InspectionReasonTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InspectionReasonUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_inspection_reason(): void
    {
        $inspectionReason = InspectionReason::factory()->create();

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

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionReasonUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $inspectionReason->id,
                        'inspection_reason' => $inspectionReasonData
                    ]
                )
                ->select(
                    [
                        'id',
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk();

        foreach ($translates as $translate) {
            $this->assertDatabaseHas(
                InspectionReasonTranslate::class,
                [
                    'row_id' => $inspectionReason->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }

    public function test_update_inspection_reason_only_with_default_language(): void
    {
        $inspectionReason = InspectionReason::factory()->create();

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

        $inspectionReasonData = [
            'active' => true,
            'need_description' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionReasonUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $inspectionReason->id,
                        'inspection_reason' => $inspectionReasonData
                    ]
                )
                ->select(
                    [
                        'id',
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk();

        foreach ($translates as $translate) {
            $this->assertDatabaseHas(
                InspectionReasonTranslate::class,
                [
                    'row_id' => $inspectionReason->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }
    public function test_empty_default_language(): void
    {
        $inspectionReason = InspectionReason::factory()->create();

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
            GraphQLQuery::mutation(InspectionReasonUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $inspectionReason->id,
                        'inspection_reason' => $inspectionReasonData
                    ]
                )
                ->select(
                    [
                        'id',
                        'translates' => [
                            'language',
                            'title',
                        ]
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
