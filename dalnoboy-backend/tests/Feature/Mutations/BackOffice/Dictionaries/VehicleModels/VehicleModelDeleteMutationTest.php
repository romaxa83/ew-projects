<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleModels;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels\VehicleModelDeleteMutation;
use App\Models\Dictionaries\VehicleModel;
use App\Models\Vehicles\Vehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleModelDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_vehicle_model(): void
    {
        $vehicleModel = VehicleModel::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleModelDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleModel->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleModelDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            VehicleModel::class,
            [
                'id' => $vehicleModel->id,
            ]
        );
    }

    public function test_delete_vehicle_model_with_vehicles(): void
    {
        $vehicleModel = VehicleModel::factory()->create();
        Vehicle::factory()->for($vehicleModel)->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleModelDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleModel->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.has_related_entities'),
                        ]
                    ]
                ]
            );
    }
}
