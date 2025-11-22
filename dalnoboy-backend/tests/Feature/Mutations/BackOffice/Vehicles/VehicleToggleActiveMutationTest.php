<?php

namespace Tests\Feature\Mutations\BackOffice\Vehicles;

use App\GraphQL\Mutations\BackOffice\Vehicles\VehicleToggleActiveMutation;
use App\Models\Vehicles\Vehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$vehicle->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $vehicle->refresh();
        $this->assertFalse((bool) $vehicle->active);
    }
}
