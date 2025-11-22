<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Queries\Common\Dictionaries\BaseVehicleMakesQuery;
use App\Models\Dictionaries\VehicleMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleMakesQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private iterable $vehicleMakes;

    public function setUp(): void
    {
        parent::setUp();

        $this->vehicleMakes = VehicleMake::factory()
            ->count(15)
            ->create();
    }

    public function test_get_vehicle_makes_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseVehicleMakesQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'title',
                            'vehicle_form',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseVehicleMakesQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'title',
                                    'vehicle_form',
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                VehicleMake::query()
                    ->count(),
                'data.' . BaseVehicleMakesQuery::NAME . '.data'
            );
    }

    public function test_get_vehicle_makes_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->vehicleMakes[0]->active = false;
        $this->vehicleMakes[0]->title = 'B title';
        $this->vehicleMakes[0]->save();

        $this->vehicleMakes[1]->active = false;
        $this->vehicleMakes[1]->title = 'A title';
        $this->vehicleMakes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleMakesQuery::NAME)
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
                        BaseVehicleMakesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleMakes[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleMakes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleMakesQuery::NAME . '.data');
    }

    public function test_filter_by_moderated(): void
    {
        $this->loginAsAdminWithRole();
        $this->vehicleMakes[0]->is_moderated = false;
        $this->vehicleMakes[0]->title = 'B title';
        $this->vehicleMakes[0]->save();

        $this->vehicleMakes[1]->is_moderated = false;
        $this->vehicleMakes[1]->title = 'A title';
        $this->vehicleMakes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleMakesQuery::NAME)
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
                        BaseVehicleMakesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleMakes[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleMakes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleMakesQuery::NAME . '.data');
    }

    public function test_search(): void
    {
        $this->loginAsAdminWithRole();

        $word = $this->faker->unique->word . '123';

        $this->vehicleMakes[0]->title = $word . ' 1';
        $this->vehicleMakes[0]->save();
        $this->vehicleMakes[1]->title = $word . ' 2';
        $this->vehicleMakes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleMakesQuery::NAME)
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
                        BaseVehicleMakesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleMakes[0]->id,
                                ],
                                [
                                    'id' => $this->vehicleMakes[1]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleMakesQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = VehicleMake::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->vehicleMakes[0]->active = false;
        $this->vehicleMakes[0]->save();

        $this->vehicleMakes[1]->active = false;
        $this->vehicleMakes[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseVehicleMakesQuery::NAME)
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
            ->assertJsonCount($startCount - 2, 'data.' . BaseVehicleMakesQuery::NAME . '.data');
    }

    public function test_filter_by_vehicle_form(): void
    {
        $countMainMake = VehicleMake::query()
            ->where('vehicle_form', VehicleFormEnum::MAIN)
            ->count();
        $this->loginAsAdminWithRole();
        $this->vehicleMakes[0]->vehicle_form = VehicleFormEnum::MAIN;
        $this->vehicleMakes[0]->title = 'B title';
        $this->vehicleMakes[0]->save();

        $this->vehicleMakes[1]->vehicle_form = VehicleFormEnum::MAIN;
        $this->vehicleMakes[1]->title = 'A title';
        $this->vehicleMakes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleMakesQuery::NAME)
                ->args(
                    [
                        'vehicle_form' => VehicleFormEnum::MAIN(),
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
                        BaseVehicleMakesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleMakes[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleMakes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount($countMainMake + 2, 'data.' . BaseVehicleMakesQuery::NAME . '.data');
    }
}
