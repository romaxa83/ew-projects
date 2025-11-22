<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Queries\Common\Dictionaries\BaseVehicleClassesQuery;
use App\Models\Dictionaries\VehicleClass;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleClassesQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $vehicleClasses;

    public function setUp(): void
    {
        parent::setUp();

        $this->vehicleClasses = VehicleClass::factory()
            ->count(15)
            ->create();
    }

    public function test_get_vehicle_classes_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseVehicleClassesQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'vehicle_form',
                            'translate' => [
                                'title',
                                'language'
                            ],
                            'translates' => [
                                'title',
                                'language'
                            ],
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseVehicleClassesQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'vehicle_form',
                                    'translate' => [
                                        'title',
                                        'language'
                                    ],
                                    'translates' => [
                                        '*' => [
                                            'title',
                                            'language'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                VehicleClass::query()
                    ->count(),
                'data.' . BaseVehicleClassesQuery::NAME . '.data'
            );
    }

    public function test_get_vehicle_classes_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_vehicle_form(): void
    {
        $startCount = VehicleClass::query()
            ->where('vehicle_form', VehicleFormEnum::TRAILER)
            ->count();

        $this->loginAsAdminWithRole();
        $this->vehicleClasses[0]->vehicle_form = VehicleFormEnum::TRAILER;
        $this->vehicleClasses[0]->save();
        $this->vehicleClasses[1]->vehicle_form = VehicleFormEnum::TRAILER;
        $this->vehicleClasses[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleClassesQuery::NAME)
                ->args(
                    [
                        'per_page' => 1000,
                        'vehicle_form' => VehicleFormEnum::TRAILER(),
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
                        BaseVehicleClassesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleClasses[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleClasses[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2 + $startCount, 'data.' . BaseVehicleClassesQuery::NAME . '.data');
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->vehicleClasses[0]->active = false;
        $this->vehicleClasses[0]->save();
        $this->vehicleClasses[1]->active = false;
        $this->vehicleClasses[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleClassesQuery::NAME)
                ->args(
                    [
                        'active' => false,
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
                        BaseVehicleClassesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleClasses[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleClasses[0]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleClassesQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = VehicleClass::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->vehicleClasses[0]->active = false;
        $this->vehicleClasses[0]->save();
        $this->vehicleClasses[1]->active = false;
        $this->vehicleClasses[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseVehicleClassesQuery::NAME)
                ->args(['per_page' => 1000])
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
            ->assertJsonCount($startCount - 2, 'data.' . BaseVehicleClassesQuery::NAME . '.data');
    }
}
