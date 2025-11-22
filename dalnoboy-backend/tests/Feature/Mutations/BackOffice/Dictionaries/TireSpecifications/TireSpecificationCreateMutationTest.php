<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireSpecifications;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications\TireSpecificationCreateMutation;
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

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire_specification(): void
    {
        $tireMake = TireMake::factory()->create();
        $tireModel = TireModel::factory()->create();
        $tireType = TireType::factory()->create();
        $tireSize = TireSize::factory()->create();

        $tireSpecificationData = [
            'active' => true,
            'make_id' => $tireMake->getKey(),
            'model_id' => $tireModel->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 6,
        ];

        $tireSpecificationId = $this->postGraphQLBackOffice(
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

    public function test_empty_model(): void
    {
        $tireMake = TireMake::factory()->create();
        $tireType = TireType::factory()->create();
        $tireSize = TireSize::factory()->create();

        $tireSpecificationData = [
            'active' => true,
            'make_id' => $tireMake->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 6,
        ];

        $this->postGraphQLBackOffice(
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
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field TireSpecificationInputType.model_id of required type ID! was not provided.'
                        ]
                    ]
                ]
            );
    }
    public function test_create_with_same_values(): void
    {
        $tireMake = TireMake::factory()
            ->create();
        $tireModel = TireModel::factory()
            ->create();
        $tireType = TireType::factory()
            ->create();
        $tireSize = TireSize::factory()
            ->create();

        $tireSpecification = TireSpecification::factory()
            ->for($tireMake, 'tireMake')
            ->for($tireModel, 'tireModel')
            ->for($tireSize, 'tireSize')
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSpecificationCreateMutation::NAME)
                ->args(
                    [
                        'tire_specification' => [
                            'active' => true,
                            'make_id' => $tireMake->getKey(),
                            'model_id' => $tireModel->getKey(),
                            'type_id' => $tireType->getKey(),
                            'size_id' => $tireSize->getKey(),
                            'ngp' => 6,
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
            ->assertJson(
                [
                    'data' => [
                        TireSpecificationCreateMutation::NAME => [
                            'id' => $tireSpecification->id
                        ]
                    ]
                ]
            );
    }
}
