<?php


namespace Tests\Feature\Mutations\BackOffice\Vehicles\Schemas;


use App\GraphQL\Mutations\BackOffice\Vehicles\Schemas\SchemaVehicleDeleteMutation;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SchemaVehicleDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_schema(): void
    {
        $schema = SchemaVehicle::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SchemaVehicleDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $schema->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SchemaVehicleDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            SchemaVehicle::class,
            [
                'id' => $schema->id
            ]
        );
    }
}
