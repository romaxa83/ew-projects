<?php

namespace Tests\Feature\Mutations\BackOffice\Tires;

use App\GraphQL\Mutations\BackOffice\Tires\TireCreateMutation;
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

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire(): void
    {
        $specification = TireSpecification::factory()->create();
        $relationshipType = TireRelationshipType::factory()->create();

        $tireData = [
            'active' => true,
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
        ];

        $tireId = $this->postGraphQLBackOffice(
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

    public function test_create_tire_without_relationship_type(): void
    {
        $specification = TireSpecification::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireCreateMutation::NAME)
                ->args(
                    [
                        'tire' => [
                            'active' => true,
                            'specification_id' => $specification->getKey(),
                            'serial_number' => 'JDFDF8678SD',
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
                    'data' => [
                        TireCreateMutation::NAME => [
                            'id',
                        ]
                    ]
                ]
            );
    }

    public function test_empty_serial_number(): void
    {
        $specification = TireSpecification::factory()
            ->create();
        $relationshipType = TireRelationshipType::factory()
            ->create();

        $tireData = [
            'active' => true,
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
        ];

        $this->postGraphQLBackOffice(
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
                            'message' => 'Field TireInputType.serial_number of required type String! was not provided.'
                        ]
                    ]
                ]
            );
    }

    public function test_create_tire_ogp_not_changed(): void
    {
        $specification = TireSpecification::factory()->create();
        $relationshipType = TireRelationshipType::factory()->create();

        $tireData = [
            'active' => true,
            'specification_id' => $specification->getKey(),
            'relationship_type_id' => $relationshipType->getKey(),
            'serial_number' => 'JDFDF8678SD',
            'ogp' => 0.5,
        ];

        $tireId = $this->postGraphQLBackOffice(
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
                'ogp' => $specification->ngp,
            ]
        );
    }
}
