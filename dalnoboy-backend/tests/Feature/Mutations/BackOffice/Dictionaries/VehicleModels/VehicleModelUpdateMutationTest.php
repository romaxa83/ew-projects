<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleModels;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels\VehicleModelUpdateMutation;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleModelUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_vehicle_model(): void
    {
        $vehicleModel = VehicleModel::factory()->create();

        $vehicleModelData = [
            'active' => true,
            'vehicle_make_id' => VehicleMake::factory()
                ->create()->id,
            'title' => $this->faker->name,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleModelUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleModel->id,
                        'vehicle_model' => $vehicleModelData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                        'vehicle_make' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleModelUpdateMutation::NAME => [
                            'id' => $vehicleModel->id,
                            'active' => $vehicleModelData['active'],
                            'title' => $vehicleModelData['title'],
                            'vehicle_make' => [
                                'id' => $vehicleModelData['vehicle_make_id']
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_update_vehicle_model_without_vehicle_form(): void
    {
        $vehicleModel = VehicleModel::factory()->create();

        $vehicleModelData = [
            'active' => true,
            'title' => 'test title 2',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleModelUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleModel->id,
                        'vehicle_model' => $vehicleModelData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field VehicleModelInputType.vehicle_make_id of required type ID! was not provided.'
                        ]
                    ]
                ]
            );
    }

}
