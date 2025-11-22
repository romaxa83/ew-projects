<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleClasses;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses\VehicleClassDeleteMutation;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleType;
use App\Models\Vehicles\Vehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleClassDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_vehicle_class(): void
    {
        $vehicleClass = VehicleClass::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleClass->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleClassDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            VehicleClass::class,
            [
                'id' => $vehicleClass->id,
            ]
        );
    }

    public function test_delete_vehicle_class_with_vehicle_types(): void
    {
        $vehicleClass = VehicleClass::factory()->create();
        $vehicleType = VehicleType::factory()->create();
        $vehicleType->vehicleClasses()->attach($vehicleClass);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleClass->id,
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
            VehicleClass::class,
            [
                'id' => $vehicleClass->id,
            ]
        );
    }

    public function test_delete_vehicle_type_with_vehicles(): void
    {
        $vehicleClass = VehicleClass::factory()->create();
        Vehicle::factory()->for($vehicleClass)->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleClass->id,
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
