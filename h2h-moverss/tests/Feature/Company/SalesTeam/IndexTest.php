<?php

namespace Tests\Feature\Company\SalesTeam;

use App\Enums\Employee\SalesTeamEnum;
use App\Models\Division;
use App\Models\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\SalesTeam\EfficiencyPlanBuilder;
use Tests\Builders\SalesTeam\SalesPlanBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected EfficiencyPlanBuilder $efficiencyPlanBuilder;
    protected SalesPlanBuilder $salesPlanBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->efficiencyPlanBuilder = resolve(EfficiencyPlanBuilder::class);
        $this->salesPlanBuilder = resolve(SalesPlanBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success_get_sales_plan()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        /** @var $employee_1 Employee */
        $employee_1 = $this->employeeBuilder
            ->sales_team(SalesTeamEnum::Local)->create();
        $sales_plan_1_1 = $this->salesPlanBuilder
            ->employee($employee_1)
            ->date($now->subMonths(1))
            ->create();
        /** @var $sales_plan_1_2 Employee\SalesPlan */
        $sales_plan_1_2 = $this->salesPlanBuilder
            ->date($now->subMonths(2))
            ->employee($employee_1)
            ->create();

        $employee_2 = $this->employeeBuilder
            ->sales_team(SalesTeamEnum::Local)->create();
        $sales_plan_2_1 = $this->salesPlanBuilder
            ->date($now->subMonths(1))
            ->employee($employee_2)
            ->create();
        $sales_plan_2_2 = $this->salesPlanBuilder
            ->date($now->subMonths(2))
            ->employee($employee_2)
            ->create();
        $sales_plan_2_3 = $this->salesPlanBuilder
            ->date($now->subMonths(3))
            ->employee($employee_2)
            ->create();

        $employee_3 = $this->employeeBuilder
            ->sales_team(SalesTeamEnum::Local_long)
            ->create();
        $sales_plan_3_1 = $this->salesPlanBuilder
            ->date($now->subMonths(1))
            ->employee($employee_3)
            ->create();


        $this->post(route('company.sales-team.index'), [
            'date' => $now->subMonths(2)->format('Y-m'),
        ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'sales_plans' => [
                        'local' => [
                            [
                                'employee_id' => $employee_1->id,
                                'name' => $employee_1->name,
                                'last_name' => $employee_1->l_name,
                                'sales_plan_id' => $sales_plan_1_2->id,
                                'local' => $sales_plan_1_2->local,
                                'intrestate' => $sales_plan_1_2->intrestate,
                                'date' => $sales_plan_1_2->getDate(),
                                'prev_sales_plan_id' => $sales_plan_1_1->id,
                                'prev_local' => $sales_plan_1_1->local,
                                'prev_intrestate' => $sales_plan_1_1->intrestate,
                            ],
                            [
                                'employee_id' => $employee_2->id,
                                'name' => $employee_2->name,
                                'last_name' => $employee_2->l_name,
                                'sales_plan_id' => $sales_plan_2_2->id,
                                'local' => $sales_plan_2_2->local,
                                'intrestate' => $sales_plan_2_2->intrestate,
                                'date' => $sales_plan_2_2->getDate(),
                                'prev_sales_plan_id' => $sales_plan_2_1->id,
                                'prev_local' => $sales_plan_2_1->local,
                                'prev_intrestate' => $sales_plan_2_1->intrestate,
                            ]
                        ],
                        'long' => [
                            [
                                'employee_id' => $employee_3->id,
                                'name' => $employee_3->name,
                                'last_name' => $employee_3->l_name,
                                'local' => null,
                                'intrestate' => null,
                                'date' => $sales_plan_2_2->getDate(),
                                'prev_sales_plan_id' => $sales_plan_3_1->id,
                                'prev_local' => $sales_plan_3_1->local,
                                'prev_intrestate' => $sales_plan_3_1->intrestate,
                            ],
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.sales_plans.local')
            ->assertJsonCount(1, 'data.sales_plans.long')
        ;
    }

    /** @test */
    public function success_get_sales_plan_and_create()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        /** @var $employee_1 Employee */
        $employee_1 = $this->employeeBuilder
            ->sales_team(SalesTeamEnum::Local)->create();
        $employee_2 = $this->employeeBuilder
            ->sales_team(SalesTeamEnum::Local)->create();
        $employee_3 = $this->employeeBuilder
            ->sales_team(SalesTeamEnum::Local_long)->create();
        $this->employeeBuilder->create();

        $this->post(route('company.sales-team.index'), [
            'date' => $now->format('Y-m'),
        ])
//            ->dump()
            ->assertJson([
                'success' => true,
                'data' => [
                    'sales_plans' => [
                        'local' => [
                            [
                                'employee_id' => $employee_1->id,
                                'name' => $employee_1->name,
                                'last_name' => $employee_1->l_name,
                                'local' => null,
                                'intrestate' => null,
                                'date' => $now->format('Y-m'),
                                'prev_sales_plan_id' => null,
                                'prev_local' => null,
                                'prev_intrestate' => null,
                            ],
                            [
                                'employee_id' => $employee_2->id,
                                'name' => $employee_2->name,
                                'last_name' => $employee_2->l_name,
                                'local' => null,
                                'intrestate' => null,
                                'date' => $now->format('Y-m'),
                                'prev_sales_plan_id' => null,
                                'prev_local' => null,
                                'prev_intrestate' => null,
                            ]
                        ],
                        'long' => [
                            [
                                'employee_id' => $employee_3->id,
                                'name' => $employee_3->name,
                                'last_name' => $employee_3->l_name,
                                'local' => null,
                                'intrestate' => null,
                                'date' => $now->format('Y-m'),
                                'prev_sales_plan_id' => null,
                                'prev_local' => null,
                                'prev_intrestate' => null,
                            ],
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.sales_plans.local')
            ->assertJsonCount(1, 'data.sales_plans.long')
        ;
    }

    /** @test */
    public function success_get_efficiency_plan()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        /** @var $efficiencyPlan Employee\EfficiencyPlan */
        $efficiencyPlan = $this->efficiencyPlanBuilder
            ->date($now->format('Y-m'))
            ->conversion_long_team(22)
            ->conversion_local_team(25)
            ->create();

        /** @var $pastEfficiencyPlan Employee\EfficiencyPlan */
        $pastEfficiencyPlan = $this->efficiencyPlanBuilder
            ->date($now->subMonth()->format('Y-m'))
            ->conversion_long_team(32)
            ->conversion_local_team(35)
            ->create();

        $this->post(route('company.sales-team.index'), [
            'date' => $now->format('Y-m'),
        ])
//            ->dump()
            ->assertJson([
                'success' => true,
                'data' => [
                    'efficiency_plan' => [
                        'id' => $efficiencyPlan->id,
                        'date' => $efficiencyPlan->getDate(),
                        'conversion_local_team' => $efficiencyPlan->conversion_local_team,
                        'prev_id' => $pastEfficiencyPlan->id,
                        'prev_conversion_local_team' => $pastEfficiencyPlan->conversion_local_team,
                        'prev_conversion_long_team' => $pastEfficiencyPlan->conversion_long_team,
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function success_get_and_create_empty_efficiency_plan()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $date = CarbonImmutable::now();

        $this->post(route('company.sales-team.index'), [
            'date' => $date->format('Y-m'),
        ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'efficiency_plan' => [
                        'date' => $date->format('Y-m'),
                        'conversion_local_team' => null,
                        'conversion_long_team' => null,
                        'prev_id' => null,
                        'prev_conversion_local_team' => null,
                        'prev_conversion_long_team' => null,
                    ],
                ]
            ])
        ;
    }
}
