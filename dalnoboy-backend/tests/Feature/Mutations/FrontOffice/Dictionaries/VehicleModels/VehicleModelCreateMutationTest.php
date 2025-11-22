<?php

namespace Tests\Feature\Mutations\FrontOffice\Dictionaries\VehicleModels;

use App\GraphQL\Mutations\FrontOffice\Dictionaries\VehicleModels\VehicleModelCreateMutation;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class VehicleModelCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsUserWithRole();
    }

    public function test_create_vehicle_model(): void
    {
        $vehicleMake = VehicleMake::factory()
            ->create();

        $vehicleModelData = [
            'active' => true,
            'vehicle_make_id' => $vehicleMake->id,
            'title' => $this->faker->name
        ];

        $vehicleModelId = $this->postGraphQL(
            GraphQLQuery::mutation(
                VehicleModelCreateMutation::NAME
            )
                ->args(
                    [
                        'vehicle_model' => $vehicleModelData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                        'vehicle_make' => [
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
                        VehicleModelCreateMutation::NAME => [
                            'id',
                            'active',
                            'title',
                            'vehicle_make' => [
                                'id',
                            ],
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        VehicleModelCreateMutation::NAME => [
                            'active' => true,
                            'title' => $vehicleModelData['title'],
                            'vehicle_make' => [
                                'id' => $vehicleMake->id,
                            ],
                        ]
                    ]
                ]
            )
            ->json('data.' . VehicleModelCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            VehicleModel::class,
            [
                'id' => $vehicleModelId,
                'active' => true,
                'vehicle_make_id' => $vehicleMake->id,
                'title' => $vehicleModelData['title'],
            ]
        );
    }

    public function test_try_to_create_vehicle_model_with_similar_title(): void
    {
        $vehicleMake = VehicleMake::factory()
            ->addModels()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::mutation(
                VehicleModelCreateMutation::NAME
            )
                ->args(
                    [
                        'vehicle_model' => [
                            'active' => true,
                            'vehicle_make_id' => $vehicleMake->id,
                            'title' => $vehicleMake->vehicleModels()
                                ->first()->title
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                        'vehicle_make' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.dictionaries.not_uniq_model'),
                            'errorCode' => Response::HTTP_FAILED_DEPENDENCY
                        ]
                    ]
                ]
            );
    }

    public function test_create_vehicle_model_with_similar_title_non_active(): void
    {
        $vehicleModel = VehicleModel::factory(['active' => false])
            ->for(
                VehicleMake::factory()
                    ->create(),
                'vehicleMake'
            )
            ->create();

        $this->postGraphQL(
            GraphQLQuery::mutation(VehicleModelCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_model' => [
                            'active' => true,
                            'vehicle_make_id' => $vehicleModel->vehicleMake->id,
                            'title' => $vehicleModel->title
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
                        VehicleModelCreateMutation::NAME => [
                            'id' => $vehicleModel->id
                        ]
                    ]
                ]
            );
        $this->assertDatabaseHas(
            VehicleModel::class,
            [
                'id' => $vehicleModel->id,
                'active' => true
            ]
        );
    }
}
