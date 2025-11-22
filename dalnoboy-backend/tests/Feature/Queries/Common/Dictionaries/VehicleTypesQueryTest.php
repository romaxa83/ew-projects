<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseVehicleTypesQuery;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleTypesQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $vehicleTypes;

    public function setUp(): void
    {
        parent::setUp();

        $this->vehicleTypes = VehicleType::factory()
            ->count(15)
            ->create();
    }

    public function test_get_vehicle_types_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseVehicleTypesQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'vehicle_classes' => [
                                'id',
                            ],
                            'translate' => [
                                'title',
                                'language'
                            ],
                            'translates' => [
                                'title',
                                'language'
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
                        BaseVehicleTypesQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'vehicle_classes' => [
                                        '*' => [
                                            'id',
                                        ]
                                    ],
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
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                VehicleType::query()
                    ->count(),
                'data.' . BaseVehicleTypesQuery::NAME . '.data'
            );
    }

    public function test_get_vehicle_types_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_vehicle_class(): void
    {
        $this->loginAsAdminWithRole();
        $vehicleClass = VehicleClass::factory()->create();
        $this->vehicleTypes[0]->vehicleClasses()->attach($vehicleClass);
        $this->vehicleTypes[1]->vehicleClasses()->attach($vehicleClass);

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleTypesQuery::NAME)
                ->args(
                    [
                        'vehicle_class' => $vehicleClass->getKey(),
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
                        BaseVehicleTypesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleTypes[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleTypes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleTypesQuery::NAME . '.data');
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->vehicleTypes[0]->active = false;
        $this->vehicleTypes[0]->save();
        $this->vehicleTypes[1]->active = false;
        $this->vehicleTypes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehicleTypesQuery::NAME)
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
                        BaseVehicleTypesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicleTypes[1]->id,
                                ],
                                [
                                    'id' => $this->vehicleTypes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseVehicleTypesQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = VehicleType::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->vehicleTypes[0]->active = false;
        $this->vehicleTypes[0]->save();
        $this->vehicleTypes[1]->active = false;
        $this->vehicleTypes[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseVehicleTypesQuery::NAME)
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
            ->assertJsonCount($startCount - 2, 'data.' . BaseVehicleTypesQuery::NAME . '.data');
    }
}
