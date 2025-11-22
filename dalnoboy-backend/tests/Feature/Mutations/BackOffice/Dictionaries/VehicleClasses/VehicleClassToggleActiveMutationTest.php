<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleClasses;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses\VehicleClassToggleActiveMutation;
use App\Models\Dictionaries\VehicleClass;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleClassToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_vehicle_class(): void
    {
        $vehicleClass = VehicleClass::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$vehicleClass->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleClassToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $vehicleClass->refresh();
        $this->assertFalse((bool) $vehicleClass->active);
    }
}
