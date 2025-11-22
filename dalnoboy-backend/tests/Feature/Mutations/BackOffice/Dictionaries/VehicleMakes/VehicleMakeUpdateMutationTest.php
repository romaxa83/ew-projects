<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleMakes;

use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes\VehicleMakeUpdateMutation;
use App\Models\Dictionaries\VehicleMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class VehicleMakeUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_vehicle_make(): void
    {
        $vehicleMake = VehicleMake::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleMake->id,
                        'vehicle_make' => [
                            'active' => true,
                            'title' => $title = $this->faker->name
                        ]
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
            ->assertJson(
                [
                    'data' => [
                        VehicleMakeUpdateMutation::NAME => [
                            'id' => $vehicleMake->id,
                            'active' => true,
                            'title' => $title
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            VehicleMake::class,
            [
                'id' => $vehicleMake->id,
                'active' => true,
                'title' => $title
            ]
        );
    }

    public function test_update_vehicle_make_with_vehicle_form(): void
    {
        $vehicleMake = VehicleMake::factory()->create(['vehicle_form' => VehicleFormEnum::TRAILER]);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleMake->id,
                        'vehicle_make' => [
                            'active' => true,
                            'title' => $title = $this->faker->name,
                            'vehicle_form' => VehicleFormEnum::MAIN(),
                        ]
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
            ->assertJson(
                [
                    'data' => [
                        VehicleMakeUpdateMutation::NAME => [
                            'id' => $vehicleMake->id,
                            'active' => true,
                            'title' => $title,
                            'vehicle_form' => VehicleFormEnum::MAIN(),
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            VehicleMake::class,
            [
                'id' => $vehicleMake->id,
                'active' => true,
                'title' => $title,
                'vehicle_form' => VehicleFormEnum::MAIN(),
            ]
        );
    }

    public function test_try_to_update_vehicle_make_to_similar_title(): void
    {
        $vehicleMake = VehicleMake::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleMake->id,
                        'vehicle_make' => [
                            'active' => true,
                            'title' => VehicleMake::factory()
                                ->create()->title
                        ]
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
        $vehicleMake = VehicleMake::factory()
            ->create();

        $vehicleMakeData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleMakeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleMake->id,
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
