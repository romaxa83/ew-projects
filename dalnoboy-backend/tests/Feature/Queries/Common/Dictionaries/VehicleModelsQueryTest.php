<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseVehicleModelsQuery;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleModelsQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private iterable $vehicleModels;

    public function setUp(): void
    {
        parent::setUp();

        $this->vehicleModels = VehicleModel::factory()
            ->count(15)
            ->create();
    }

    public function test_get_vehicle_models_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseVehicleModelsQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'title',
                            'vehicle_make' => [
                                'id',
                            ],
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseVehicleModelsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'title',
                                    'vehicle_make' => [
                                            'id',
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                VehicleModel::query()
                    ->count(),
                'data.' . BaseVehicleModelsQuery::NAME . '.data'
            );
    }

    public function test_get_vehicle_models_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_vehicle_make(): void
    {
        $this->loginAsAdminWithRole();
        $vehicleMake = VehicleMake::factory()->create();
        $this->vehicleModels[0]->vehicle_make_id = $vehicleMake->getKey();
        $this->vehicleModels[0]->save();
        $this->vehicleModels[1]->vehicle_make_id = $vehicleMake->getKey();
        $this->vehicleModels[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleModelsQuery::NAME)
                ->args(
                    [
                        'vehicle_make' => $vehicleMake->getKey(),
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseVehicleModelsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleModels[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleModels[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleModelsQuery::NAME . '.data');
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->vehicleModels[0]->active = false;
        $this->vehicleModels[0]->save();
        $this->vehicleModels[1]->active = false;
        $this->vehicleModels[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleModelsQuery::NAME)
                ->args(
                    [
                        'active' => false,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseVehicleModelsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleModels[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleModels[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleModelsQuery::NAME . '.data');
    }

    public function test_filter_by_moderated(): void
    {
        $this->loginAsAdminWithRole();
        $this->vehicleModels[0]->is_moderated = false;
        $this->vehicleModels[0]->save();
        $this->vehicleModels[1]->is_moderated = false;
        $this->vehicleModels[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleModelsQuery::NAME)
                ->args(
                    [
                        'is_moderated' => false,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseVehicleModelsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleModels[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleModels[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleModelsQuery::NAME . '.data');
    }

    public function test_search(): void
    {
        $this->loginAsAdminWithRole();

        $word = $this->faker->unique->word;

        $this->vehicleModels[0]->title = $word . ' ' . $this->faker->word;
        $this->vehicleModels[0]->save();
        $this->vehicleModels[1]->title = $word . ' ' . $this->faker->word;
        $this->vehicleModels[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleModelsQuery::NAME)
                ->args(
                    [
                        'query' => $word,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseVehicleModelsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleModels[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleModels[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleModelsQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = VehicleModel::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->vehicleModels[0]->active = false;
        $this->vehicleModels[0]->save();
        $this->vehicleModels[1]->active = false;
        $this->vehicleModels[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseVehicleModelsQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount($startCount - 2, 'data.' . BaseVehicleModelsQuery::NAME . '.data');
    }
}
