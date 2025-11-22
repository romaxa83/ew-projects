<?php


namespace Tests\Feature\Mutations\BackOffice\Vehicles;


use App\GraphQL\Mutations\BackOffice\Vehicles\VehicleDeleteMutation;
use App\Models\Vehicles\Vehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_main_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleDeleteMutation::NAME)
                ->args([
                    'id' => $vehicle->id
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    VehicleDeleteMutation::NAME => true
                ]
            ]);

        $this->assertDatabaseMissing(
            Vehicle::class,
            [
                'id' => $vehicle->id
            ]
        );
    }
}
