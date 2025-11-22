<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleModels;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels\VehicleModelToggleActiveMutation;
use App\Models\Dictionaries\VehicleModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleModelToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_vehicle_model(): void
    {
        $vehicleModel = VehicleModel::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleModelToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$vehicleModel->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleModelToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $vehicleModel->refresh();
        $this->assertFalse((bool) $vehicleModel->active);
    }
}
