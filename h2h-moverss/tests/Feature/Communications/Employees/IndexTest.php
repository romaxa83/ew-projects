<?php

namespace Tests\Feature\Communications\Employees;

use App\Models\Division;
use App\Models\Employee\PbxData;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Employees\PbxDataBuilder;
use Tests\Builders\Ringostat\EventBeforeCallBuilder;
use Tests\Builders\Users\RoleBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected RoleBuilder $roleBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected PbxDataBuilder $pbxDataBuilder;
    protected EventBeforeCallBuilder $eventBeforeCallBuilder;
    protected CallEventBuilder $callEventBuilder;


    protected array $data;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->roleBuilder = resolve(RoleBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->pbxDataBuilder = resolve(PbxDataBuilder::class);
        $this->callEventBuilder = resolve(CallEventBuilder::class);
        $this->eventBeforeCallBuilder = resolve(EventBeforeCallBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success_get_only_order_manager()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_admin = $this->roleBuilder->asAdmin()->create();
        $role_manager = $this->roleBuilder->asManager()->create();
        $role_driver = $this->roleBuilder->asDriver()->create();

        $user_1 = $this->userBuilder->roles($role_admin)->create();
        $user_2 = $this->userBuilder->roles($role_manager)->create();
        $user_3 = $this->userBuilder
            ->roles($role_manager, $role_admin)->create();
        $user_4 = $this->userBuilder
            ->roles($role_manager, $role_admin, $role_driver)->create();
        $user_5 = $this->userBuilder->roles($role_driver)->create();

        $employee_1 = $this->employeeBuilder
            ->user($user_1)
            ->ringostat_id(111)
            ->create();
        $employee_2 = $this->employeeBuilder
            ->user($user_2)
            ->ringostat_id(112)
            ->create();
        $employee_3 = $this->employeeBuilder
            ->user($user_3)->create();
        $this->pbxDataBuilder
            ->pbx_ext(109)
            ->employee($employee_3)
            ->create();

        $employee_4 = $this->employeeBuilder
            ->user($user_4)->create();
        $this->pbxDataBuilder
            ->pbx_ext(null)
            ->employee($employee_4)
            ->create();

        $employee_5 = $this->employeeBuilder
            ->user($user_5)->create();

        $this->get(route('communications.employees'))
            ->assertJsonStructure([
                'success',
                'records' => [
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'is_online',
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ],
                        'call' => []
                    ]
                ],
                'meta' => [
                    'count_oncall',
                    'count_online',
                    'count_offline',
                ]
            ])
            ->assertJson([
                'success' => true,
                'records' => [
                    ['id' => $employee_1->id],
                    ['id' => $employee_2->id],
                    ['id' => $employee_3->id],
                ]
            ])
            ->assertJsonCount(3, 'records')
            ->assertJsonCount(0, 'records.0.call')
            ->assertJsonCount(0, 'records.1.call')
            ->assertJsonCount(0, 'records.2.call')
        ;
    }

    /** @test */
    public function success_only_active()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_manager = $this->roleBuilder->asManager()->create();

        $user_1 = $this->userBuilder->roles($role_manager)->create();
        $user_2 = $this->userBuilder->roles($role_manager)->create();
        $user_3 = $this->userBuilder->roles($role_manager)->create();

        $employee_1 = $this->employeeBuilder->active(1)
            ->ringostat_id(112)
            ->user($user_1)->create();
        $employee_2 = $this->employeeBuilder->active(0)
            ->ringostat_id(113)
            ->user($user_2)->create();
        $employee_3 = $this->employeeBuilder->active(0)
            ->user($user_2)->create();
        $this->pbxDataBuilder
            ->pbx_ext(109)
            ->employee($employee_3)
            ->create();

        $this->get(route('communications.employees'))
            ->assertJson([
                'success' => true,
                'records' => [
                    ['id' => $employee_1->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function success_online_status_as_ringo_and_zadarma_sip()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_manager = $this->roleBuilder->asManager()->create();

        $user_1 = $this->userBuilder->roles($role_manager)->create();
        $user_2 = $this->userBuilder->roles($role_manager)->create();
        $user_3 = $this->userBuilder->roles($role_manager)->create();

        $employee_1 = $this->employeeBuilder
            ->ringostat_id(111)
            ->ringostat_sip_status(true)
            ->zadarma_sip_status(false)
            ->user($user_1)->create();
        $employee_2 = $this->employeeBuilder
            ->ringostat_id(112)
            ->ringostat_sip_status(false)
            ->zadarma_sip_status(true)
            ->user($user_2)->create();

        $employee_3 = $this->employeeBuilder
            ->ringostat_id(113)
            ->ringostat_sip_status(false)
            ->zadarma_sip_status(false)
            ->user($user_3)->create();

        $this->get(route('communications.employees'))
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $employee_1->id,
                        'is_online' => true
                    ],
                    [
                        'id' => $employee_2->id,
                        'is_online' => true
                    ],
                    [
                        'id' => $employee_3->id,
                        'is_online' => false
                    ],
                ]
            ])
            ->assertJsonCount(3, 'records')
        ;
    }

    /** @test */
    public function success_in_call_by_ringostat_as_out()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_manager = $this->roleBuilder->asManager()->create();

        $user_1 = $this->userBuilder->roles($role_manager)->create();
        $user_2 = $this->userBuilder->roles($role_manager)->create();

        $event = $this->eventBeforeCallBuilder
            ->call_type('out')
            ->create();

        $employee_1 = $this->employeeBuilder
            ->ringostat_id(112)
            ->ringostat_call_rec_id($event->id)
            ->active(1)
            ->user($user_1)
            ->create();
        $employee_2 = $this->employeeBuilder
            ->ringostat_id(113)
            ->active(0)->user($user_2)->create();

        $this->get(route('communications.employees'))
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $employee_1->id,
                        'call' => [
                            'type' => $event->call_type,
                            'number' => $event->destination,
                            'start_at' => $event->created_at->timestamp,
                            'client_id' => null,
                            'client_name' => null,
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function success_in_call_by_zadarma_as_out()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_manager = $this->roleBuilder->asManager()->create();

        $user_1 = $this->userBuilder->roles($role_manager)->create();

        $event = $this->callEventBuilder
            ->pbx_call_id('out_0892e63cb09c4d7a0550f77314fcd216156021ca')
            ->create();

        $employee_1 = $this->employeeBuilder
            ->zadarma_call_rec_id($event->id)
            ->zadarma_sip_status(true)
            ->active(1)
            ->user($user_1)
            ->create();
        $this->pbxDataBuilder
            ->pbx_ext(100)
            ->employee($employee_1)
            ->create();

        $this->get(route('communications.employees'))
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $employee_1->id,
                        'first_name' => $employee_1->name,
                        'last_name' => $employee_1->l_name,
                        'is_online' => true,
                        'call' => [
                            'type' => 'out',
                            'number' => $event->destination,
                            'start_at' => $event->created_at->timestamp,
                            'client_id' => null,
                            'client_name' => null,
                        ]
                    ],
                ],
                'meta' => [
                    'count_oncall' => 1,
                    'count_online' => 0,
                    'count_offline' => 0,
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function success_in_call_by_ringostat_as_in_and_with_client()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_manager = $this->roleBuilder->asManager()->create();

        $user_1 = $this->userBuilder->roles($role_manager)->create();
        $user_2 = $this->userBuilder->roles($role_manager)->create();

        $client = $this->clientBuilder->create();

        $event = $this->eventBeforeCallBuilder
            ->call_type('in')
            ->client($client)
            ->create();

        $employee_1 = $this->employeeBuilder
            ->ringostat_id(112)
            ->ringostat_call_rec_id($event->id)
            ->active(1)
            ->user($user_1)
            ->create();
        $employee_2 = $this->employeeBuilder
            ->ringostat_id(113)
            ->active(0)->user($user_2)->create();


        $this->get(route('communications.employees'))
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $employee_1->id,
                        'call' => [
                            'type' => $event->call_type,
                            'number' => $event->callers_number,
                            'start_at' => $event->created_at->timestamp,
                            'client_id' => $client->id,
                            'client_name' => $client->full_name,
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function success_in_call_by_zadarma_as_in_and_with_client()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_manager = $this->roleBuilder->asManager()->create();

        $user_1 = $this->userBuilder->roles($role_manager)->create();

        $client = $this->clientBuilder->create();

        $event = $this->callEventBuilder
            ->pbx_call_id('in_0892e63cb09c4d7a0550f77314fcd216156021ca')
            ->client($client)
            ->create();

        $employee_1 = $this->employeeBuilder
            ->zadarma_call_rec_id($event->id)
            ->zadarma_sip_status(false)
            ->active(1)
            ->user($user_1)
            ->create();
        $this->pbxDataBuilder
            ->pbx_ext(100)
            ->employee($employee_1)
            ->create();

        $this->get(route('communications.employees'))
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $employee_1->id,
                        'first_name' => $employee_1->name,
                        'last_name' => $employee_1->l_name,
                        'is_online' => false,
                        'call' => [
                            'type' => 'in',
                            'number' => $event->destination,
                            'start_at' => $event->created_at->timestamp,
                            'client_id' => $client->id,
                            'client_name' => $client->full_name,
                        ]
                    ],
                ],
                'meta' => [
                    'count_oncall' => 1,
                    'count_online' => 0,
                    'count_offline' => 0,
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function success_sort_employee_without_call()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_manager = $this->roleBuilder->asManager()->create();

        $user_1 = $this->userBuilder->roles($role_manager)->create();
        $user_2 = $this->userBuilder->roles($role_manager)->create();
        $user_3 = $this->userBuilder->roles($role_manager)->create();
        $user_4 = $this->userBuilder->roles($role_manager)->create();
        $user_5 = $this->userBuilder->roles($role_manager)->create();

        $employee_1 = $this->employeeBuilder
            ->ringostat_sip_status(false)
            ->ringostat_id(112)
            ->user($user_1)
            ->create();
        $employee_2 = $this->employeeBuilder
            ->ringostat_sip_status(true)
            ->ringostat_id(112)
            ->user($user_2)
            ->create();
        $employee_3 = $this->employeeBuilder
            ->zadarma_sip_status(true)
            ->user($user_3)
            ->create();
        $this->pbxDataBuilder
            ->pbx_ext(100)
            ->employee($employee_3)
            ->create();

        $employee_4 = $this->employeeBuilder
            ->zadarma_sip_status(false)
            ->user($user_4)
            ->create();
        $this->pbxDataBuilder
            ->pbx_ext(101)
            ->employee($employee_4)
            ->create();

        $employee_5 = $this->employeeBuilder
            ->ringostat_sip_status(true)
            ->ringostat_id(112)
            ->user($user_5)
            ->create();

        $this->get(route('communications.employees'))
            ->assertJson([
                'success' => true,
                'records' => [
                    ['id' => $employee_2->id],
                    ['id' => $employee_3->id],
                    ['id' => $employee_5->id],
                    ['id' => $employee_1->id],
                    ['id' => $employee_4->id],
                ],
                'meta' => [
                    'count_oncall' => 0,
                    'count_online' => 3,
                    'count_offline' => 2
                ]
            ])
            ->assertJsonCount(5, 'records')
        ;
    }

    /** @test */
    public function success_sort_employee_with_call()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_manager = $this->roleBuilder->asManager()->create();

        $user_1 = $this->userBuilder->roles($role_manager)->create();
        $user_2 = $this->userBuilder->roles($role_manager)->create();
        $user_3 = $this->userBuilder->roles($role_manager)->create();
        $user_4 = $this->userBuilder->roles($role_manager)->create();
        $user_5 = $this->userBuilder->roles($role_manager)->create();
        $user_6 = $this->userBuilder->roles($role_manager)->create();

        $event_1 = $this->eventBeforeCallBuilder
            ->call_type('out')
            ->create();
        $event_2 = $this->eventBeforeCallBuilder
            ->call_type('in')
            ->create();
        $event_3 = $this->callEventBuilder
            ->create();

        $employee_1 = $this->employeeBuilder
            ->ringostat_sip_status(false)
            ->ringostat_id(112)
            ->user($user_1)
            ->create();
        $employee_2 = $this->employeeBuilder
            ->ringostat_sip_status(true)
            ->ringostat_id(112)
            ->ringostat_call_rec_id($event_1->id)
            ->user($user_2)
            ->create();
        $employee_3 = $this->employeeBuilder
            ->ringostat_sip_status(true)
            ->ringostat_id(112)
            ->user($user_3)
            ->create();

        $employee_4 = $this->employeeBuilder
            ->ringostat_sip_status(false)
            ->ringostat_id(112)
            ->user($user_4)
            ->create();
        $employee_5 = $this->employeeBuilder
            ->ringostat_sip_status(true)
            ->ringostat_call_rec_id($event_2->id)
            ->ringostat_id(112)
            ->user($user_5)
            ->create();
        $employee_6 = $this->employeeBuilder
            ->zadarma_sip_status(true)
            ->zadarma_call_rec_id($event_3->id)
            ->user($user_5)
            ->create();
        $this->pbxDataBuilder->pbx_ext(100)
            ->employee($employee_6)->create();

        $this->get(route('communications.employees'))
            ->assertJson([
                'success' => true,
                'records' => [
                    ['id' => $employee_2->id],
                    ['id' => $employee_5->id],
                    ['id' => $employee_6->id],
                    ['id' => $employee_3->id],
                    ['id' => $employee_1->id],
                    ['id' => $employee_4->id],
                ],
                'meta' => [
                    'count_oncall' => 3,
                    'count_online' => 1,
                    'count_offline' => 2
                ]
            ])
            ->assertJsonCount(6, 'records')
        ;
    }

    /** @test */
    public function success_filter_no_show_offline_employees()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role_manager = $this->roleBuilder->asManager()->create();

        $user_1 = $this->userBuilder->roles($role_manager)->create();
        $user_2 = $this->userBuilder->roles($role_manager)->create();
        $user_3 = $this->userBuilder->roles($role_manager)->create();
        $user_4 = $this->userBuilder->roles($role_manager)->create();
        $user_5 = $this->userBuilder->roles($role_manager)->create();

        $employee_1 = $this->employeeBuilder
            ->ringostat_sip_status(false)
            ->ringostat_id(112)
            ->user($user_1)
            ->create();
        $employee_2 = $this->employeeBuilder
            ->ringostat_sip_status(true)
            ->ringostat_id(112)
            ->user($user_2)
            ->create();
        $employee_3 = $this->employeeBuilder
            ->zadarma_sip_status(true)
            ->user($user_3)
            ->create();
        $this->pbxDataBuilder->pbx_ext(100)->employee($employee_3)->create();

        $employee_4 = $this->employeeBuilder
            ->zadarma_sip_status(false)
            ->ringostat_id(112)
            ->user($user_4)
            ->create();
        $this->pbxDataBuilder->pbx_ext(101)->employee($employee_4)->create();

        $employee_5 = $this->employeeBuilder
            ->ringostat_sip_status(true)
            ->ringostat_id(112)
            ->user($user_5)
            ->create();

        $this->get(route('communications.employees', [
            'show_offline' => false,
        ]))
            ->assertJson([
                'success' => true,
                'records' => [
                    ['id' => $employee_2->id],
                    ['id' => $employee_3->id],
                    ['id' => $employee_5->id],
                ],
                'meta' => [
                    'count_oncall' => 0,
                    'count_online' => 3,
                    'count_offline' => 0
                ]
            ])
            ->assertJsonCount(3, 'records')
        ;
    }

    /** @test */
    public function success_empty_data()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $this->get(route('communications.employees'))
            ->assertJson([
                'success' => true,
                'records' => []
            ])
            ->assertJsonCount(0, 'records')
        ;
    }

}
