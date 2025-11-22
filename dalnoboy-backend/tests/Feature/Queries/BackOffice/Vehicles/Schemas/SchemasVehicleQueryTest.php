<?php


namespace Tests\Feature\Queries\BackOffice\Vehicles\Schemas;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Queries\BackOffice\Vehicles\Schemas\SchemasVehicleQuery;
use App\Models\Vehicles\Schemas\SchemaAxle;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Schemas\SchemaWheel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SchemasVehicleQueryTest extends TestCase
{
    use DatabaseTransactions;

    private Collection $schemas;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        SchemaVehicle::factory()
            ->count(6)
            ->create();
        SchemaVehicle::factory()
            ->trailer()
            ->count(7)
            ->create();

        $this->schemas = SchemaVehicle::notDefault()
            ->orderBy('name')
            ->with(
                [
                    'axles',
                    'axles.wheels'
                ]
            )
            ->get();
    }

    public function test_get_schemas(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SchemasVehicleQuery::NAME)
                ->select(
                    [
                        'id',
                        'name',
                        'vehicle_form',
                        'axles' => [
                            'id',
                            'position',
                            'name',
                            'wheels' => [
                                'id',
                                'position',
                                'name',
                                'use',
                                'required',
                                'created_at',
                                'updated_at',
                            ],
                            'created_at',
                            'updated_at',
                        ],
                        'created_at',
                        'updated_at',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SchemasVehicleQuery::NAME => $this
                            ->schemas
                            ->map(
                                fn(SchemaVehicle $schema) => [
                                    'id' => $schema->id,
                                    'name' => $schema->name,
                                    'vehicle_form' => $schema->vehicle_form,
                                    'axles' => $schema
                                        ->axles
                                        ->map(
                                            fn(SchemaAxle $axle) => [
                                                'id' => $axle->id,
                                                'position' => $axle->position,
                                                'name' => $axle->name,
                                                'wheels' => $axle
                                                    ->wheels
                                                    ->map(
                                                        fn(SchemaWheel $wheel) => [
                                                            'id' => $wheel->id,
                                                            'position' => $wheel->position,
                                                            'name' => $wheel->name,
                                                            'use' => $wheel->use,
                                                            'required' => $wheel->required(),
                                                            'created_at' => $wheel->created_at->getTimestamp(),
                                                            'updated_at' => $wheel->updated_at->getTimestamp(),
                                                        ]
                                                    )
                                                    ->toArray(),
                                                'created_at' => $axle->created_at->getTimestamp(),
                                                'updated_at' => $axle->updated_at->getTimestamp(),
                                            ]
                                        )
                                        ->toArray(),
                                    'created_at' => $schema->created_at->getTimestamp(),
                                    'updated_at' => $schema->updated_at->getTimestamp(),
                                ]
                            )
                            ->toArray()
                    ]
                ]
            );
    }

    public function test_get_schema_by_id(): void
    {
        /**@var SchemaVehicle $schema */
        $schema = $this->schemas->random(1)
            ->first();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SchemasVehicleQuery::NAME)
                ->args(
                    [
                        'id' => $schema->id
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SchemasVehicleQuery::NAME => [
                            [
                                'id' => $schema->id
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SchemasVehicleQuery::NAME);
    }

    public function test_get_schema_by_name(): void
    {
        /**@var SchemaVehicle $schema */
        $schema = $this->schemas->random(1)
            ->first();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SchemasVehicleQuery::NAME)
                ->args(
                    [
                        'name' => $schema->name
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SchemasVehicleQuery::NAME => [
                            [
                                'id' => $schema->id
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SchemasVehicleQuery::NAME);
    }

    public function test_get_schema_by_form(): void
    {
        $result = $this
            ->schemas
            ->filter(
                fn(SchemaVehicle $schema) => $schema->vehicle_form->is(VehicleFormEnum::TRAILER)
            )
            ->values()
            ->map(
                fn(SchemaVehicle $schema) => [
                    'id' => $schema->id
                ]
            )
            ->toArray();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SchemasVehicleQuery::NAME)
                ->args(
                    [
                        'vehicle_form' => VehicleFormEnum::TRAILER()
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SchemasVehicleQuery::NAME => $result
                    ]
                ]
            )
            ->assertJsonCount(count($result), 'data.' . SchemasVehicleQuery::NAME);
    }
}
