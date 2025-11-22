<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireRelationshipTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes\TireRelationshipTypeUpdateMutation;
use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireRelationshipTypeTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireRelationshipTypeUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire_relationship_type(): void
    {
        $tireRelationshipType = TireRelationshipType::factory()->create();

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

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireRelationshipTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireRelationshipType->id,
                        'tire_relationship_type' => $tireRelationshipTypeData
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
                TireRelationshipTypeTranslate::class,
                [
                    'row_id' => $tireRelationshipType->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }

    public function test_update_tire_relationship_type_only_with_default_language(): void
    {
        $tireRelationshipType = TireRelationshipType::factory()->create();

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

        $tireRelationshipTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireRelationshipTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireRelationshipType->id,
                        'tire_relationship_type' => $tireRelationshipTypeData
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
                TireRelationshipTypeTranslate::class,
                [
                    'row_id' => $tireRelationshipType->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }
    public function test_empty_default_language(): void
    {
        $tireRelationshipType = TireRelationshipType::factory()->create();

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
            GraphQLQuery::mutation(TireRelationshipTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireRelationshipType->id,
                        'tire_relationship_type' => $tireRelationshipTypeData
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
