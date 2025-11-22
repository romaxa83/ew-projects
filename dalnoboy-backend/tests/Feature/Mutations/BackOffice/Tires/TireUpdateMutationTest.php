<?php

namespace Tests\Feature\Mutations\BackOffice\Tires;

use App\GraphQL\Mutations\BackOffice\Tires\TireUpdateMutation;
use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Tires\Tire;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire(): void
    {
        $tire = Tire::factory()->create();

        $specification = TireSpecification::factory()->create();
        $relationshipType = TireRelationshipType::factory()->create();

        $tireData = [
            'active' => true,
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tire->id,
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
            ->assertOk();

        $this->assertDatabaseHas(
            Tire::class,
            [
                'id' => $tire->getKey(),
                'active' => true,
                'specification_id' => $specification->getKey(),
                'relationship_type_id' => $relationshipType->getKey(),
                'serial_number' => 'JDFDF8678SD',
            ]
        );
    }

    public function test_empty_specification(): void
    {
        $tire = Tire::factory()->create();

        $relationshipType = TireRelationshipType::factory()->create();

        $tireData = [
            'active' => true,
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tire->id,
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
                            'message' => 'Field TireInputType.specification_id of required type ID! was not provided.'
                        ]
                    ]
                ]
            );
    }

    public function test_update_tire_ogp(): void
    {
        $tire = Tire::factory()->create();

        $specification = TireSpecification::factory()->create();
        $relationshipType = TireRelationshipType::factory()->create();

        $tireData = [
            'active' => true,
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
            'ogp' => 0.5,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tire->id,
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
            ->assertOk();

        $this->assertDatabaseHas(
            Tire::class,
            [
                'id' => $tire->getKey(),
                'active' => true,
                'specification_id' => $specification->getKey(),
                'relationship_type_id' => $relationshipType->getKey(),
                'serial_number' => 'JDFDF8678SD',
                'ogp' => 0.5,
            ]
        );
    }

    public function test_update_tire_ogp_is_zero(): void
    {
        $tire = Tire::factory()->create(['ogp' => 0.3]);

        $specification = TireSpecification::factory()->create();
        $relationshipType = TireRelationshipType::factory()->create();

        $tireData = [
            'active' => true,
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
            'ogp' => 0,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tire->id,
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
            ->assertOk();

        $this->assertDatabaseHas(
            Tire::class,
            [
                'id' => $tire->getKey(),
                'active' => true,
                'specification_id' => $specification->getKey(),
                'relationship_type_id' => $relationshipType->getKey(),
                'serial_number' => 'JDFDF8678SD',
                'ogp' => 0,
            ]
        );
    }

    public function test_update_tire_ogp_not_present(): void
    {
        $tire = Tire::factory()->create(['ogp' => 0.3]);

        $specification = TireSpecification::factory()->create();
        $relationshipType = TireRelationshipType::factory()->create();

        $tireData = [
            'active' => true,
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tire->id,
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
            ->assertOk();

        $this->assertDatabaseHas(
            Tire::class,
            [
                'id' => $tire->getKey(),
                'active' => true,
                'specification_id' => $specification->getKey(),
                'relationship_type_id' => $relationshipType->getKey(),
                'serial_number' => 'JDFDF8678SD',
                'ogp' => 0.3,
            ]
        );
    }
}
