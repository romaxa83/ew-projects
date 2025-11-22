<?php

namespace Tests\Feature\Mutations\FrontOffice\Tires;

use App\GraphQL\Mutations\FrontOffice\Tires\TireCreateMutation;
use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Tires\Tire;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsUserWithRole();
    }

    public function test_create_tire(): void
    {
        $specification = TireSpecification::factory()->create();
        $relationshipType = TireRelationshipType::factory()->create();

        $tireData = [
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
        ];

        $tireId = $this->postGraphQL(
            GraphQLQuery::mutation(TireCreateMutation::NAME)
                ->args(
                    [
                        'tire' => $tireData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'serial_number',
                        'specification' => [
                            'id',
                        ],
                        'relationship_type' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        TireCreateMutation::NAME => [
                            'id',
                            'active',
                            'serial_number',
                            'specification' => [
                                'id',
                            ],
                            'relationship_type' => [
                                'id',
                            ],
                        ]
                    ]
                ]
            )
            ->json('data.' . TireCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Tire::class,
            [
                'id' => $tireId,
                'active' => true,
                'specification_id' => $specification->getKey(),
                'relationship_type_id' => $relationshipType->getKey(),
                'serial_number' => 'JDFDF8678SD',
            ]
        );
    }

    public function test_create_same_tire(): void
    {
        $specification = TireSpecification::factory()->create();
        $relationshipType = TireRelationshipType::factory()->create();

        Tire::factory()->create([
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
        ]);

        $tireData = [
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
        ];

        $this->postGraphQL(
            GraphQLQuery::mutation(TireCreateMutation::NAME)
                ->args(
                    [
                        'tire' => $tireData
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
                            'message' => trans('validation.custom.same_entity_exists'),
                        ]
                    ]
                ]
            );
    }

    public function test_create_same_tire_in_offline_with_diff_specification_not_moderated(): void
    {
        $tire = Tire::factory()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::mutation(TireCreateMutation::NAME)
                ->args(
                    [
                        'tire' => [
                            'specification_id' => TireSpecification::factory()
                                ->create()
                                ->getKey(),
                            'relationship_type_id' => $tire->relationship_type_id,
                            'serial_number' => $tire->serial_number,
                            'is_offline' => true,
                        ]
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
            ->assertJsonStructure(
                [
                    'errors'
                ]
            );
    }
}
