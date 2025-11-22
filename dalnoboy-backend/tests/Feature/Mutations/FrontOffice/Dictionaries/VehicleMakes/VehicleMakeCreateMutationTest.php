<?php

namespace Tests\Feature\Mutations\FrontOffice\Dictionaries\VehicleMakes;

use App\GraphQL\Mutations\FrontOffice\Dictionaries\VehicleMakes\VehicleMakeCreateMutation;
use App\Models\Dictionaries\VehicleMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class VehicleMakeCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsUserWithRole();
    }

    public function test_create_vehicle_make(): void
    {
        $vehicleMakeId = $this->postGraphQL(
            GraphQLQuery::mutation(VehicleMakeCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_make' => [
                            'title' => $title = $this->faker->name
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                        'is_moderated',
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
                            'is_moderated',
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
                'title' => $title,
            ]
        );
    }

    public function test_create_same_vehicle_make(): void
    {
        $vehicleMake = VehicleMake::factory()
            ->create();

        $this->postGraphQL(
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

    public function test_create_same_non_active_vehicle_make(): void
    {
        $vehicleMake = VehicleMake::factory(['active' => false])
            ->create();

        $this->postGraphQL(
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
                    'data' => [
                        VehicleMakeCreateMutation::NAME => [
                            'id' => $vehicleMake->id
                        ]
                    ]
                ]
            );
    }
}
