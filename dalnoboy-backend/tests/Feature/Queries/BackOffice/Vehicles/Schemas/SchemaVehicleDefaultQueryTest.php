<?php


namespace Tests\Feature\Queries\BackOffice\Vehicles\Schemas;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Queries\BackOffice\Vehicles\Schemas\SchemaVehicleDefaultQuery;
use App\Models\Vehicles\Schemas\SchemaAxle;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Schemas\SchemaWheel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SchemaVehicleDefaultQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_default_main_schema(): void
    {
        $this->loginAsAdminWithRole();

        $schema = SchemaVehicle::default()
            ->vehicleForm(VehicleFormEnum::MAIN())
            ->first();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SchemaVehicleDefaultQuery::NAME)
                ->args(
                    [
                        'vehicle_form' => VehicleFormEnum::MAIN()
                    ]
                )
                ->select(
                    [
                        'id',
                        'name',
                        'image',
                        'axles' => [
                            'id',
                            'position',
                            'name',
                            'wheels' => [
                                'id',
                                'position',
                                'name',
                                'use'
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SchemaVehicleDefaultQuery::NAME => [
                            'id' => $schema->id,
                            'name' => $schema->name,
                            'image' => $schema->image,
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
                                                    'use' => $wheel->use
                                                ]
                                            )
                                            ->toArray()
                                    ]
                                )
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_get_default_trailer_schema(): void
    {
        $this->loginAsAdminWithRole();

        $schema = SchemaVehicle::default()
            ->vehicleForm(VehicleFormEnum::TRAILER())
            ->first();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SchemaVehicleDefaultQuery::NAME)
                ->args(
                    [
                        'vehicle_form' => VehicleFormEnum::TRAILER()
                    ]
                )
                ->select(
                    [
                        'id',
                        'name',
                        'image',
                        'axles' => [
                            'id',
                            'position',
                            'name',
                            'wheels' => [
                                'id',
                                'position',
                                'name',
                                'use'
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SchemaVehicleDefaultQuery::NAME => [
                            'id' => $schema->id,
                            'name' => $schema->name,
                            'image' => $schema->image,
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
                                                    'use' => $wheel->use
                                                ]
                                            )
                                            ->toArray()
                                    ]
                                )
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_get_default_trailer_with_more_axles_schema(): void
    {
        $this->loginAsAdminWithRole();

        $response = $this->postGraphQLBackOffice(
            GraphQLQuery::query(SchemaVehicleDefaultQuery::NAME)
                ->args(
                    [
                        'vehicle_form' => VehicleFormEnum::TRAILER(),
                        'axles_count' => 7
                    ]
                )
                ->select(
                    [
                        'id',
                        'name',
                        'image',
                        'axles' => [
                            'id',
                            'position',
                            'name',
                            'wheels' => [
                                'id',
                                'position',
                                'name',
                                'use'
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk();

        $this->assertDatabaseHas(
            SchemaVehicle::class,
            [
                'is_default' => true,
                'vehicle_form' => VehicleFormEnum::TRAILER,
                'name' => 'default_' . VehicleFormEnum::TRAILER . '_7'
            ]
        );

        $schema = SchemaVehicle::default()
            ->vehicleForm(VehicleFormEnum::TRAILER())
            ->has('axles', 7)
            ->first();

        $response
            ->assertJson(
                [
                    'data' => [
                        SchemaVehicleDefaultQuery::NAME => [
                            'id' => $schema->id,
                            'name' => $schema->name,
                            'image' => $schema->image,
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
                                                    'use' => $wheel->use
                                                ]
                                            )
                                            ->toArray()
                                    ]
                                )
                                ->toArray()
                        ]
                    ]
                ]
            );
    }
}
