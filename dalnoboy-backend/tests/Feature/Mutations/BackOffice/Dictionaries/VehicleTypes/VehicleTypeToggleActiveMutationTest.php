<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes\VehicleTypeToggleActiveMutation;
use App\Models\Dictionaries\VehicleType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleTypeToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_vehicle_type(): void
    {
        $vehicleType = VehicleType::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleTypeToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$vehicleType->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleTypeToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $vehicleType->refresh();
        $this->assertFalse((bool) $vehicleType->active);
    }
}
