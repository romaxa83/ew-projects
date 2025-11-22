<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes\VehicleTypeDeleteMutation;
use App\Models\Dictionaries\VehicleType;
use App\Models\Vehicles\Vehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleTypeDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_vehicle_type(): void
    {
        $vehicleType = VehicleType::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleTypeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleType->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleTypeDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            VehicleType::class,
            [
                'id' => $vehicleType->id,
            ]
        );
    }

    public function test_delete_vehicle_type_with_vehicles(): void
    {
        $vehicleType = VehicleType::factory()->create();
        Vehicle::factory()->for($vehicleType)->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleTypeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleType->id,
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
