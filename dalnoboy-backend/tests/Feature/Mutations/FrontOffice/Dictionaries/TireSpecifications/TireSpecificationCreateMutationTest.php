<?php

namespace Tests\Feature\Mutations\FrontOffice\Dictionaries\TireSpecifications;

use App\GraphQL\Mutations\FrontOffice\Dictionaries\TireSpecifications\TireSpecificationCreateMutation;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Dictionaries\TireType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireSpecificationCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsuserWithRole();
    }

    public function test_create_tire_specification(): void
    {
        $tireMake = TireMake::factory()->create();
        $tireModel = TireModel::factory()->create();
        $tireType = TireType::factory()->create();
        $tireSize = TireSize::factory()->create();

        $tireSpecificationData = [
            'make_id' => $tireMake->getKey(),
            'model_id' => $tireModel->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 6,
        ];

        $tireSpecificationId = $this->postGraphQL(
            GraphQLQuery::mutation(TireSpecificationCreateMutation::NAME)
                ->args(
                    [
                        'tire_specification' => $tireSpecificationData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'make' => [
                            'id',
                        ],
                        'model' => [
                            'id',
                        ],
                        'type' => [
                            'id',
                        ],
                        'size' => [
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
                        TireSpecificationCreateMutation::NAME => [
                            'id',
                            'active',
                            'make' => [
                                'id',
                            ],
                            'model' => [
                                'id',
                            ],
                            'type' => [
                                'id',
                            ],
                            'size' => [
                                'id',
                            ],
                        ]
                    ]
                ]
            )
            ->json('data.' . TireSpecificationCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireSpecification::class,
            [
                'id' => $tireSpecificationId,
                'active' => true,
                'make_id' => $tireMake->getKey(),
                'model_id' => $tireModel->getKey(),
                'type_id' => $tireType->getKey(),
                'size_id' => $tireSize->getKey(),
                'ngp' => 6,
            ]
        );
    }

    public function test_create_same_tire_specification_in_offline(): void
    {
        $tireMake = TireMake::factory()->create();
        $tireModel = TireModel::factory()->create();
        $tireType = TireType::factory()->create();
        $tireSize = TireSize::factory()->create();

        $specification = TireSpecification::factory()->create([
            'make_id' => $tireMake->getKey(),
            'model_id' => $tireModel->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 6,
        ]);

        $tireSpecificationData = [
            'make_id' => $tireMake->getKey(),
            'model_id' => $tireModel->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 6,
            'is_offline' => true,
        ];

        $tireSpecificationId = $this->postGraphQL(
            GraphQLQuery::mutation(TireSpecificationCreateMutation::NAME)
                ->args(
                    [
                        'tire_specification' => $tireSpecificationData
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
            ->json('data.' . TireSpecificationCreateMutation::NAME . '.id');

        $specification->refresh();
        $this->assertEquals($specification->id, $tireSpecificationId);
        $this->assertFalse($specification->isModerated());
    }

    public function test_create_same_tire_specification_in_offline_with_diff_ngp_moderated(): void
    {
        $tireMake = TireMake::factory()->create();
        $tireModel = TireModel::factory()->create();
        $tireType = TireType::factory()->create();
        $tireSize = TireSize::factory()->create();

        $specification = TireSpecification::factory()->create([
            'make_id' => $tireMake->getKey(),
            'model_id' => $tireModel->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 6,
        ]);

        $tireSpecificationData = [
            'make_id' => $tireMake->getKey(),
            'model_id' => $tireModel->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 5,
            'is_offline' => true,
        ];

        $tireSpecificationId = $this->postGraphQL(
            GraphQLQuery::mutation(TireSpecificationCreateMutation::NAME)
                ->args(
                    [
                        'tire_specification' => $tireSpecificationData
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
            ->json('data.' . TireSpecificationCreateMutation::NAME . '.id');

        $specification->refresh();
        $this->assertEquals($specification->id, $tireSpecificationId);
        $this->assertFalse($specification->isModerated());
        $this->assertEquals(5, $specification->ngp);
    }

    public function test_create_same_tire_specification_in_offline_with_diff_ngp_not_moderated(): void
    {
        $tireMake = TireMake::factory()->create();
        $tireModel = TireModel::factory()->create();
        $tireType = TireType::factory()->create();
        $tireSize = TireSize::factory()->create();

        $specification = TireSpecification::factory()->create([
            'make_id' => $tireMake->getKey(),
            'model_id' => $tireModel->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 6,
            'is_moderated' => false,
        ]);

        $tireSpecificationData = [
            'make_id' => $tireMake->getKey(),
            'model_id' => $tireModel->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 5,
            'is_offline' => true,
        ];

        $tireSpecificationId = $this->postGraphQL(
            GraphQLQuery::mutation(TireSpecificationCreateMutation::NAME)
                ->args(
                    [
                        'tire_specification' => $tireSpecificationData
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
            ->json('data.' . TireSpecificationCreateMutation::NAME . '.id');

        $specification->refresh();
        $this->assertEquals($specification->id, $tireSpecificationId);
        $this->assertFalse($specification->isModerated());
        $this->assertEquals(5, $specification->ngp);
    }
}
