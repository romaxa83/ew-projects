<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleMakes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes\VehicleMakeDeleteMutation;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use App\Models\Vehicles\Vehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleMakeDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_vehicle_make(): void
    {
        $vehicleMake = VehicleMake::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleMake->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleMakeDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            VehicleMake::class,
            [
                'id' => $vehicleMake->id,
            ]
        );
    }

    public function test_delete_vehicle_make_with_vehicle_models(): void
    {
        $vehicleMake = VehicleMake::factory()->create();
        VehicleModel::factory()->for($vehicleMake)->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleMake->id,
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

        $this->assertDatabaseHas(
            VehicleMake::class,
            [
                'id' => $vehicleMake->id,
            ]
        );
    }

    public function test_delete_vehicle_make_with_vehicles(): void
    {
        $vehicleMake = VehicleMake::factory()->create();
        Vehicle::factory()->for($vehicleMake)->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleMake->id,
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

        $this->assertDatabaseHas(
            VehicleMake::class,
            [
                'id' => $vehicleMake->id,
            ]
        );
    }
}
