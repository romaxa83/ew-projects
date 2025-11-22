<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleMakes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes\VehicleMakeToggleActiveMutation;
use App\Models\Dictionaries\VehicleMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleMakeToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_vehicle_make(): void
    {
        $vehicleMake = VehicleMake::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$vehicleMake->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        VehicleMakeToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $vehicleMake->refresh();
        $this->assertFalse((bool) $vehicleMake->active);
    }
}
