<?php

namespace Tests\Feature\Orders\Order;

use App\Enums\Catalog\BuildingTypeEnum;
use App\Enums\Catalog\FlightTypeEnum;
use App\Enums\Catalog\ParkingTypeEnum;
use App\Enums\Catalog\WorkTypeEnum;
use App\Models\Division;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Users\RoleBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class AjaxInfoTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected RoleBuilder $roleBuilder;
    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->roleBuilder = resolve(RoleBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_auth_user_is_partner()
    {
        $role = $this->roleBuilder
            ->asPartner()
            ->create();
        $user = $this->userBuilder
            ->roles($role)
            ->create();
        $this->loginUser($user);

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'id' => $model->id,
        ];

        $this->post(route('orders.record.info'), $data)
            ->assertJson([
                'success' => true,
                'id' => $model->id,
                'auth_user' => [
                    'is_partner' => true,
                ]
            ])
        ;
    }

    /** @test */
    public function check_structure_log_data()
    {
        $role = $this->roleBuilder
            ->asPartner()
            ->create();
        $user = $this->userBuilder
            ->roles($role)
            ->create();
        $this->loginUser($user);

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'id' => $model->id,
        ];

        $this->post(route('orders.record.info'), $data)
            ->assertJsonStructure([
                'logs' => [
                    'data' => [
                        [
                            'audit_id',
                            'action',
                            'entity',
                            'details' => [
                                [
                                    'field',
                                    'new',
                                    'old',
                                ]
                            ],
                            'user' => [
                                'id',
                                'name',
                                'email',
                                'employee',
                            ],
                            'client',
                            'created_at',
                            'is_client_activity',
                        ],
                    ],
                    'meta' => [
                        'current_page',
                        'from',
                        'last_page',
                        'per_page',
                        'to',
                        'total',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true
            ])
        ;
    }

    /** @test */
    public function check_structure_log_data_as_list()
    {
        $role = $this->roleBuilder
            ->asPartner()
            ->create();
        $user = $this->userBuilder
            ->roles($role)
            ->create();
        $this->loginUser($user);

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'id' => $model->id,
            'logs_all' => true,
        ];

        $this->post(route('orders.record.info'), $data)
            ->assertJsonStructure([
                'logs' => [
                    'data' => [
                        [
                            'audit_id',
                            'action',
                            'entity',
                            'details' => [
                                [
                                    'field',
                                    'new',
                                    'old',
                                ]
                            ],
                            'user' => [
                                'id',
                                'name',
                                'email',
                                'employee',
                            ],
                            'client',
                            'created_at',
                            'is_client_activity',
                        ],
                    ],
                ]
            ])
            ->assertJson([
                'success' => true
            ])
        ;
    }


    public function check_types_as_building_types()
    {
        $role = $this->roleBuilder
            ->asPartner()
            ->create();
        $user = $this->userBuilder
            ->roles($role)
            ->create();
        $this->loginUser($user);

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'id' => $model->id,
        ];

        $this->post(route('orders.record.info'), $data)
            ->assertJson([
                'success' => true,
                'types' => [
                    'waypoints' => [
                        'building_types' => BuildingTypeEnum::forSelect()
                    ]
                ]
            ])
        ;
    }

    public function check_types_as_parking_types()
    {
        $role = $this->roleBuilder
            ->asPartner()
            ->create();
        $user = $this->userBuilder
            ->roles($role)
            ->create();
        $this->loginUser($user);

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'id' => $model->id,
        ];

        $this->post(route('orders.record.info'), $data)
            ->assertJson([
                'success' => true,
                'types' => [
                    'waypoints' => [
                        'parking_types' => ParkingTypeEnum::forSelect()
                    ]
                ]
            ])
        ;
    }


    public function check_types_as_flight_type()
    {
        $role = $this->roleBuilder
            ->asPartner()
            ->create();
        $user = $this->userBuilder
            ->roles($role)
            ->create();
        $this->loginUser($user);

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'id' => $model->id,
        ];

        $this->post(route('orders.record.info'), $data)
            ->assertJson([
                'success' => true,
                'types' => [
                    'waypoints' => [
                        'flights' => FlightTypeEnum::forSelect()
                    ]
                ]
            ])
        ;
    }

    public function check_types_as_work_type()
    {
        $role = $this->roleBuilder
            ->asPartner()
            ->create();
        $user = $this->userBuilder
            ->roles($role)
            ->create();
        $this->loginUser($user);

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'id' => $model->id,
        ];

        $this->post(route('orders.record.info'), $data)
            ->assertJson([
                'success' => true,
                'types' => [
                    'works' => WorkTypeEnum::forSelect()
                ]
            ])
        ;
    }
}
