<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleMakes;

use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes\VehicleMakeCreateMutation;
use App\Imports\VehicleMakesAndModelsImport;
use App\Models\Dictionaries\VehicleMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class VehicleMakeCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_vehicle_make(): void
    {

        Excel::import(
            new VehicleMakesAndModelsImport(),
            database_path('files/VehicleMakesAndModels.csv')
        );

        $vehicleMakeData = [
            'active' => true,
            'title' => $this->faker->name,
        ];

        $vehicleMakeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_make' => $vehicleMakeData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        VehicleMakeCreateMutation::NAME => [
                            'id',
                            'active',
                            'title',
                        ]
                    ]
                ]
            )
            ->json('data.' . VehicleMakeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            VehicleMake::class,
            [
                'id' => $vehicleMakeId,
                'active' => true,
                'title' => $vehicleMakeData['title'],
            ]
        );
    }

    public function test_create_vehicle_make_with_vehicle_form(): void
    {
        $vehicleMakeData = [
            'active' => true,
            'title' => $this->faker->name,
            'vehicle_form' => VehicleFormEnum::TRAILER(),
        ];

        $vehicleMakeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_make' => $vehicleMakeData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                        'vehicle_form',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        VehicleMakeCreateMutation::NAME => [
                            'id',
                            'active',
                            'title',
                            'vehicle_form',
                        ]
                    ]
                ]
            )
            ->json('data.' . VehicleMakeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            VehicleMake::class,
            [
                'id' => $vehicleMakeId,
                'active' => true,
                'title' => $vehicleMakeData['title'],
                'vehicle_form' => $vehicleMakeData['vehicle_form'],
            ]
        );
    }

    public function test_try_create_similar_vehicle_make(): void
    {
        $vehicleMake = VehicleMake::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_make' => [
                            'title' => $vehicleMake->title
                        ]
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
                    'errors' => [
                        [
                            'message' => trans('validation.custom.dictionaries.not_uniq_make'),
                            'errorCode' => Response::HTTP_FAILED_DEPENDENCY
                        ]
                    ]
                ]
            );
    }

    public function test_empty_title(): void
    {
        $vehicleMakeData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_make' => $vehicleMakeData
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
                    'errors' => [
                        [
                            'message' => 'Field VehicleMakeInputType.title of required type String! was not provided.'
                        ]
                    ]
                ]
            );
    }
}
