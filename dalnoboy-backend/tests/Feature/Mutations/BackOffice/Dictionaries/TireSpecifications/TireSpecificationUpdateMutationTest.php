<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireSpecifications;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications\TireSpecificationUpdateMutation;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Dictionaries\TireType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireSpecificationUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire_specification(): void
    {
        $tireSpecification = TireSpecification::factory()->create();

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
            'ngp' => 6.5,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSpecificationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireSpecification->id,
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
            ->assertOk();

        $this->assertDatabaseHas(
            TireSpecification::class,
            [
                'id' => $tireSpecification->getKey(),
                'make_id' => $tireMake->getKey(),
                'model_id' => $tireModel->getKey(),
                'type_id' => $tireType->getKey(),
                'size_id' => $tireSize->getKey(),
                'ngp' => 6.5,
            ]
        );
    }

    public function test_update_tire_specification_with_empty_make(): void
    {
        $tireSpecification = TireSpecification::factory()->create();

        $tireModel = TireModel::factory()->create();
        $tireType = TireType::factory()->create();
        $tireSize = TireSize::factory()->create();

        $tireSpecificationData = [
            'active' => true,
            'model_id' => $tireModel->getKey(),
            'type_id' => $tireType->getKey(),
            'size_id' => $tireSize->getKey(),
            'ngp' => 6.5,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSpecificationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireSpecification->id,
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
                            'message' => 'Field TireSpecificationInputType.make_id of required type ID! was not provided.'
                        ]
                    ]
                ]
            );
    }

    public function test_update_tire_specification_with_current_params(): void
    {
        $tireSpecification = TireSpecification::factory()->create();

        $tireSpecificationData = [
            'active' => true,
            'make_id' => $tireSpecification->tireMake->getKey(),
            'model_id' => $tireSpecification->tireModel->getKey(),
            'type_id' => $tireSpecification->tireType->getKey(),
            'size_id' => $tireSpecification->tireSize->getKey(),
            'ngp' => 6.3,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSpecificationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireSpecification->id,
                        'tire_specification' => $tireSpecificationData
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk();

        $this->assertDatabaseHas(
            TireSpecification::class,
            [
                'id' => $tireSpecification->getKey(),
                'ngp' => 6.3,
            ]
        );
    }

    public function test_update_tire_specification_with_not_unique_params(): void
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
            GraphQLQuery::mutation(TireSpecificationUpdateMutation::NAME)
                ->args(
                    [
                        'id' => TireSpecification::factory()
                            ->create()->id,
                        'tire_specification' => [
                            'active' => true,
                            'make_id' => $tireMake->getKey(),
                            'model_id' => $tireModel->getKey(),
                            'type_id' => $tireType->getKey(),
                            'size_id' => $tireSize->getKey(),
                            'ngp' => 6.5,
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
                        TireSpecificationUpdateMutation::NAME => [
                            'id' => $tireSpecification->id
                        ]
                    ]
                ]
            );
    }
}
