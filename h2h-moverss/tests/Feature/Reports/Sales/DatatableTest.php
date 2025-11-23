<?php
//
//namespace Tests\Feature\Reports\Sales;
//
//use App\Enums\Employee\SalesTeamEnum;
//use App\Enums\Orders\EstimateTypeEnum;
//use App\Enums\Orders\MoveTypeEnum;
//use App\Enums\Reports\ReportColumnEnum;
//use App\Models\Division;
//use App\Models\Employee;
//use App\Models\Order;
//use App\Models\Order\Estimate;
//use Carbon\CarbonImmutable;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
//use Tests\Builders\Divisions\DivisionBuilder;
//use Tests\Builders\Employees\EmployeeBuilder;
//use Tests\Builders\Orders\EstimateBuilder;
//use Tests\Builders\Orders\EstimateCalculatedBuilder;
//use Tests\Builders\Orders\OrderBuilder;
//use Tests\Builders\Orders\PaymentBuilder;
//use Tests\Builders\Orders\StatusBuilder;
//use Tests\Builders\Orders\StatusHistoryBuilder;
//use Tests\Builders\Orders\TagBuilder;
//use Tests\Builders\SalesTeam\EfficiencyPlanBuilder;
//use Tests\Builders\SalesTeam\SalesPlanBuilder;
//use Tests\Builders\Users\RoleBuilder;
//use Tests\Builders\Users\UserBuilder;
//use Tests\TestCase;
//
//class DatatableTest extends TestCase
//{
//    use DatabaseTransactions;
//    protected DivisionBuilder $divisionBuilder;
//    protected UserBuilder $userBuilder;
//    protected EmployeeBuilder $employeeBuilder;
//    protected RoleBuilder $roleBuilder;
//    protected OrderBuilder $orderBuilder;
//    protected StatusBuilder $orderStatusBuilder;
//    protected StatusHistoryBuilder $orderStatusHistoryBuilder;
//    protected EstimateCalculatedBuilder $estimateCalculatedBuilder;
//    protected EstimateBuilder $estimateBuilder;
//    protected PaymentBuilder $paymentBuilder;
//    protected TagBuilder $tagBuilder;
//    protected SalesPlanBuilder $salesPlanBuilder;
//    protected EfficiencyPlanBuilder $efficiencyPlanBuilder;
//
//
//    public function setUp(): void
//    {
//        $this->divisionBuilder = resolve(DivisionBuilder::class);
//        $this->userBuilder = resolve(UserBuilder::class);
//        $this->employeeBuilder = resolve(EmployeeBuilder::class);
//        $this->roleBuilder = resolve(RoleBuilder::class);
//        $this->orderBuilder = resolve(OrderBuilder::class);
//        $this->orderStatusBuilder = resolve(StatusBuilder::class);
//        $this->orderStatusHistoryBuilder = resolve(StatusHistoryBuilder::class);
//        $this->estimateCalculatedBuilder = resolve(EstimateCalculatedBuilder::class);
//        $this->estimateBuilder = resolve(EstimateBuilder::class);
//        $this->paymentBuilder = resolve(PaymentBuilder::class);
//        $this->tagBuilder = resolve(TagBuilder::class);
//        $this->salesPlanBuilder = resolve(SalesPlanBuilder::class);
//        $this->efficiencyPlanBuilder = resolve(EfficiencyPlanBuilder::class);
//
//        parent::setUp();
//    }
//
//    // проверяем заказы со статусом done и подсчет суммы по данным заказам
//    // также данные в таблице по SalesPlan (вносятся по каждому сотруднику) и ConversionPlan (вносятся для команды)
//    // две роли(admin, manger), два сотрудника, каждый в разных sales_team (разный salesPlan)
//    // не попадает в выборку/SalesPlan/ConversionPlan заказ за другой месяц
//    /** @test */
//    public function check_orders_by_status_as_calculation_done()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//
//        // date
//        $now = CarbonImmutable::now();
//        $targetDate = $now->subMonth()->format('Y-m');
//
//        // roles
//        $adminRole = $this->roleBuilder
//            ->asAdmin()->create();
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // users/employees/salesPlans
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        /** @var $employee_1 Employee */
//        $employee_1 = $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_1)
//            ->create();
//        $sales_plan_1_1 = $this->salesPlanBuilder
//            ->employee($employee_1)
//            ->date($targetDate)
//            ->create();
//        $sales_plan_1_2 = $this->salesPlanBuilder
//            ->employee($employee_1)
//            ->date($now)
//            ->create();
//
//        $user_2 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($adminRole)->create();
//        $employee_2 = $this->employeeBuilder
//            ->user($user_2)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//        $sales_plan_2_1 = $this->salesPlanBuilder
//            ->employee($employee_2)
//            ->date($targetDate)
//            ->create();
//        $sales_plan_2_2 = $this->salesPlanBuilder
//            ->employee($employee_2)
//            ->date($now)
//            ->create();
//
//        // statuses
//        $statusNewLead = $this->orderStatusBuilder
//            ->asNewLead()
//            ->create();
//        $statusDone = $this->orderStatusBuilder
//            ->asDone()
//            ->create();
//
//        // efficiencyPlan
//        $efficiencyPlan_1 = $this->efficiencyPlanBuilder
//            ->date($now)
//            ->conversion_local_team(6)
//            ->conversion_long_team(8)
//            ->create();
//        /** @var $efficiencyPlan_2 Employee\EfficiencyPlan */
//        $efficiencyPlan_2 = $this->efficiencyPlanBuilder
//            ->date($targetDate)
//            ->conversion_local_team(16)
//            ->conversion_long_team(18)
//            ->create();
//        $efficiencyPlan_3 = $this->efficiencyPlanBuilder
//            ->date($now->subMonths(4))
//            ->conversion_local_team(26)
//            ->conversion_long_team(28)
//            ->create();
//
//        // orders/ordersStatusHistory/estimateCalculated
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_1)
//            ->title('total')
//            ->value("$100")
//            ->create();
//
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_2)
//            ->title('total')
//            ->value("$100.50")
//            ->create();
//
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_3)
//            ->title('total')
//            ->value("$1,050")
//            ->create();
//
//        // not check, last date
//        $order_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subMonths(3))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_4)
//            ->title('total')
//            ->value("$1,000")
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $targetDate,
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
//            ->dump()
//            ->assertJsonCount(7, 'data.0')
//            ->assertJsonCount(7, 'data.1')
//            ->assertJsonCount(7, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['color'], '#2b9ddf');
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['type'], ReportColumnEnum::LeadsLost());
//        $this->assertEquals($data[1]['color'], '#ffbf12');
//
//        $this->assertEquals($data[2]['id'], 3);
//        $this->assertEquals($data[2]['type'], ReportColumnEnum::LeadsLostCR());
//        $this->assertEquals($data[2]['color'], '#ffbf12');
//
//        $this->assertEquals($data[3]['id'], 4);
//        $this->assertEquals($data[3]['type'], ReportColumnEnum::LeadsCalculated());
//        $this->assertEquals($data[3]['color'], '#2b9ddf');
//
//        $this->assertEquals($data[4]['id'], 5);
//        $this->assertEquals($data[4]['type'], ReportColumnEnum::LeadsCalculatedCR());
//        $this->assertEquals($data[4]['color'], '#2b9ddf');
//
//        $this->assertEquals($data[5]['id'], 6);
//        $this->assertEquals($data[5]['type'], ReportColumnEnum::LeadsCalculatedSum());
//        $this->assertEquals($data[5]['color'], '#2b9ddf');
//
//        $this->assertEquals($data[19]['id'], 20);
//        $this->assertEquals($data[19]['type'], ReportColumnEnum::SalesPlan());
//        $this->assertEquals($data[19]['color'], '#ffbf12');
//
//        $this->assertEquals($data[23]['id'], 24);
//        $this->assertEquals($data[23]['type'], ReportColumnEnum::ConversionPlan());
//        $this->assertEquals($data[23]['color'], '#2b9ddf');
//    }
//
//    // проверяем заказы с фильтрацией на год
//    /** @test */
//    public function check_orders_by_status_as_calculation_done_filter_by_year_as_current()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//
//        // date
//        $now = CarbonImmutable::now();
//        $targetDate = $now->subMonth()->format('Y-m');
//        $targetDateY = $now->subMonth()->format('Y');
//
//        // roles
//        $adminRole = $this->roleBuilder
//            ->asAdmin()->create();
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // users/employees/salesPlans
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        /** @var $employee_1 Employee */
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_1)
//            ->create();
//
//        $user_2 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($adminRole)->create();
//        $this->employeeBuilder
//            ->user($user_2)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//
//        // statuses
//        $statusNewLead = $this->orderStatusBuilder
//            ->asNewLead()
//            ->create();
//        $statusDone = $this->orderStatusBuilder
//            ->asDone()
//            ->create();
//
//        // orders/ordersStatusHistory/estimateCalculated
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subDays(10))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subMonths(3))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusDone)
//            ->created_at($now->subMonths(5))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//
//        $order_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subMonths(3))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $targetDateY,
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
////            ->dump()
//        ;
//
//        $data = $res->json('data');
//
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['user_'.$user_1->id], 2);
//        $this->assertEquals($data[0]['user_'.$user_2->id], 1);
//        $this->assertEquals($data[0]['user_0'], "");
//    }
//
//    // проверяем заказы со статусом done и подсчет суммы по данным заказам
//    // также используется фильтр по move_type со значением - local
//    // один сотрудник, у которого есть salesPlan но нет по нему значение
//    // не попадает в выборку два заказа с другим значением move_type
//    /** @test */
//    public function check_orders_by_status_as_calculation_done_filter_by_move_type_as_local()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//        // date
//        $now = CarbonImmutable::now();
//        $targetDate = $now->subMonth()->format('Y-m');
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // users/employees
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_1 = $this->employeeBuilder
//            ->user($user_1)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//        $sales_plan_1 = $this->salesPlanBuilder
//            ->employee($employee_1)
//            ->local(null)
//            ->date($targetDate)
//            ->create();
//
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder
//            ->asNewLead()
//            ->create();
//        $statusDone  = $this->orderStatusBuilder
//            ->asDone()
//            ->create();
//
//        // orders/orderStatusHistory/estimateCalculated/estimate
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_1)
//            ->title('total')
//            ->value("$100")
//            ->create();
//        Estimate::factory([
//            'order_id' => $order_1->id,
//            'type' => EstimateTypeEnum::Local()
//        ])->create();
//
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_2)
//            ->title('total')
//            ->value("$100.50")
//            ->create();
//        Estimate::factory([
//            'order_id' => $order_2->id,
//            'type' => EstimateTypeEnum::Intrastate()
//        ])->create();
//
//        // not check, another move_type
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_3)
//            ->title('total')
//            ->value("$1,050")
//            ->create();
//        Estimate::factory([
//            'type' => EstimateTypeEnum::Interstate(),
//            'order_id' => $order_3->id,
//        ])->create();
//
//        // not check, another move_type
//        $order_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDone)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_4)
//            ->title('total')
//            ->value("$1,000")
//            ->create();
//        Estimate::factory([
//            'type' => EstimateTypeEnum::Interstate(),
//            'order_id' => $order_4->id,
//        ])->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $targetDate,
//                'period-type' => 'by_creation',
//                'move_type' => MoveTypeEnum::Local(),
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
//            ->dump()
//            ->assertJsonCount(7, 'data.0')
//            ->assertJsonCount(7, 'data.1')
//            ->assertJsonCount(7, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        // Get the actual user IDs from the response
//        $userIds = [];
//        foreach ($data[0] as $key => $value) {
//            if (strpos($key, 'user_') === 0 && $key !== 'user_0') {
//                $userIds[] = $key;
//            }
//        }
//
//        // Use the first user ID from the response for assertions
//        $firstUserId = !empty($userIds) ? $userIds[0] : null;
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['color'], '#2b9ddf');
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['type'], ReportColumnEnum::LeadsLost());
//        $this->assertEquals($data[1]['color'], '#ffbf12');
//
//        $this->assertEquals($data[2]['id'], 3);
//        $this->assertEquals($data[2]['type'], ReportColumnEnum::LeadsLostCR());
//        $this->assertEquals($data[2]['color'], '#ffbf12');
//
//        $this->assertEquals($data[3]['id'], 4);
//        $this->assertEquals($data[3]['type'], ReportColumnEnum::LeadsCalculated());
//        $this->assertEquals($data[3]['color'], '#2b9ddf');
//
//        $this->assertEquals($data[4]['id'], 5);
//        $this->assertEquals($data[4]['type'], ReportColumnEnum::LeadsCalculatedCR());
//        $this->assertEquals($data[4]['color'], '#2b9ddf');
//
//        $this->assertEquals($data[5]['id'], 6);
//        $this->assertEquals($data[5]['type'], ReportColumnEnum::LeadsCalculatedSum());
//        $this->assertEquals($data[5]['color'], '#2b9ddf');
//
//        $this->assertEquals($data[7]['id'], 8);
//        $this->assertEquals($data[7]['type'], ReportColumnEnum::LeadsBookedCR());
//        $this->assertEquals($data[7]['color'], '#ffbf12');
//
//        $this->assertEquals($data[13]['id'], 14);
//        $this->assertEquals($data[13]['type'], ReportColumnEnum::LeadsSuccessfulCR());
//        $this->assertEquals($data[13]['color'], '#ffbf12');
//
//        $this->assertEquals($data[19]['id'], 20);
//        $this->assertEquals($data[19]['type'], ReportColumnEnum::SalesPlan());
//        $this->assertEquals($data[19]['color'], '#ffbf12');
//    }
//
//    // проверяем заказы со статусом success и подсчет суммы по данным заказам
//    // проверяем просчет SalesFact и Left
//    // также используется фильтр по sales_team со значением - local
//    // два сотрудника
//    // не попадает в выборку сотрудник с другим значением и его заказы
//    /** @test */
//    public function check_orders_by_status_as_new_lead_and_success_filter_by_local_team()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//        // date
//        $now = CarbonImmutable::now();
//        $targetDate = $now->subMonth()->format('Y-m');
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // users/employees
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_1 = $this->employeeBuilder
//            ->user($user_1)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//        $sales_plan_1_1 = $this->salesPlanBuilder
//            ->employee($employee_1)
//            ->date($targetDate)
//            ->create();
//        $sales_plan_1_2 = $this->salesPlanBuilder
//            ->employee($employee_1)
//            ->date($now)
//            ->create();
//
//        // not, unsuitable sales_team
//        $user_2 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->user($user_2)
//            ->create();
//
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder->asNewLead()->create();
//        $statusSuccess  = $this->orderStatusBuilder->asSuccess()->create();
//        $statusBooked  = $this->orderStatusBuilder->asBooked()->create();
//
//        // orders/ordersStatusHistory/payment
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $this->paymentBuilder
//            ->order($order_1)->amount(50.0000)->create();
//        $this->paymentBuilder
//            ->order($order_1)->amount(150.0000)->create();
//
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $this->paymentBuilder
//            ->order($order_2)->amount(300.5000)->create();
//
//        // not, another user team
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $this->paymentBuilder
//            ->order($order_3)->amount(150.5000)->create();
//
//        $order_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $bookedTotal = 100;
//        $this->estimateCalculatedBuilder
//            ->order($order_4)
//            ->title('total')
//            ->value("$".$bookedTotal)
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $targetDate,
//                'period-type' => 'by_creation',
//                'sales_team' => SalesTeamEnum::Local(),
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
////            ->dump()
//            ->assertJsonCount(5, 'data.0')
//            ->assertJsonCount(5, 'data.1')
//            ->assertJsonCount(5, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['user_'.$user_1->id], 3);
//
//        $this->assertEquals($data[2]['id'], 3);
//        $this->assertEquals($data[2]['type'], ReportColumnEnum::LeadsLostCR());
//        $this->assertEquals($data[2]['user_'.$user_1->id], "0%");
//
//        $this->assertEquals($data[4]['id'], 5);
//        $this->assertEquals($data[4]['type'], ReportColumnEnum::LeadsCalculatedCR());
//        $this->assertEquals($data[4]['user_'.$user_1->id], "0%");
//
//        $this->assertEquals($data[6]['id'], 7);
//        $this->assertEquals($data[6]['type'], ReportColumnEnum::LeadsBooked());
//        $this->assertEquals($data[6]['user_'.$user_1->id], 1);
//
//        $this->assertEquals($data[7]['id'], 8);
//        $this->assertEquals($data[7]['type'], ReportColumnEnum::LeadsBookedCR());
//        $this->assertEquals($data[7]['user_'.$user_1->id], "33.33%");
//
//        $this->assertEquals($data[8]['id'], 9);
//        $this->assertEquals($data[8]['type'], ReportColumnEnum::LeadsBookedSum());
//        $this->assertEquals($data[8]['user_'.$user_1->id], "$".$bookedTotal);
//
//        $this->assertEquals($data[12]['id'], 13);
//        $this->assertEquals($data[12]['type'], ReportColumnEnum::LeadsSuccessful());
//        $this->assertEquals($data[12]['user_'.$user_1->id], 2);
//
//        $this->assertEquals($data[13]['id'], 14);
//        $this->assertEquals($data[13]['type'], ReportColumnEnum::LeadsSuccessfulCR());
//        $this->assertEquals($data[13]['user_'.$user_1->id], "66.67%");
//
//        $successRevenue = 50+150+300.5;
//        $this->assertEquals($data[14]['id'], 15);
//        $this->assertEquals($data[14]['type'], ReportColumnEnum::SuccessRevenue());
//        $this->assertEquals($data[14]['user_'.$user_1->id], "$".to_int($successRevenue));
//
//        $successAOV = $successRevenue/2;
//        $this->assertEquals($data[15]['id'], 16);
//        $this->assertEquals($data[15]['type'], ReportColumnEnum::SuccessAOV());
//        $this->assertEquals($data[15]['user_'.$user_1->id], "$".to_int($successAOV));
//
//        $this->assertEquals($data[19]['id'], 20);
//        $this->assertEquals($data[19]['type'], ReportColumnEnum::SalesPlan());
//        $this->assertEquals($data[19]['user_'.$user_1->id], "$".$sales_plan_1_1->local);
//
//        $salesFactCR = round(($successRevenue/$sales_plan_1_1->local)*100, 2);
//        $this->assertEquals($data[21]['id'], 22);
//        $this->assertEquals($data[21]['type'], ReportColumnEnum::SalesFactCR());
//        $this->assertEquals($data[21]['user_'.$user_1->id], $salesFactCR."%");
//
//        $left = $sales_plan_1_1->local - $successRevenue - $bookedTotal;
//        $this->assertEquals($data[20]['id'], 21);
//        $this->assertEquals($data[20]['type'], ReportColumnEnum::Left());
//        $this->assertEquals($data[20]['user_'.$user_1->id], '$'.to_int($left));
//    }
//
//    // проверяем расчет ранга (sales rank) для сотрудников local/long
//    // 10 сотрудника
//    /** @test */
//    public function check_orders_sales_rank_for_local_and_long_employee()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//        // date
//        $now = CarbonImmutable::now();
//        $targetDate = $now->subMonth()->format('Y-m');
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder->asNewLead()->create();
//        $statusSuccess  = $this->orderStatusBuilder->asSuccess()->create();
//        $statusSalesDone  = $this->orderStatusBuilder->asSalesDone()->create();
//
//        // users/employees/orders
//        // user_1, local, 2 orders - success
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_1 = $this->employeeBuilder
//            ->user($user_1)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//        $sales_plan_sum_1 = 1000;
//        $this->salesPlanBuilder
//            ->employee($employee_1)
//            ->local($sales_plan_sum_1)
//            ->date($targetDate)
//            ->create();
//
//        $order_1_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $payment_sum_1_1_1 = 50.0000;
//        $this->paymentBuilder
//            ->order($order_1_1)->amount($payment_sum_1_1_1)->create();
//        $payment_sum_1_1_2 = 150.0000;
//        $this->paymentBuilder
//            ->order($order_1_1)->amount($payment_sum_1_1_2)->create();
//
//        $order_1_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusSalesDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSalesDone)
//            ->create();
//        $payment_sum_1_2_1 = 240.0000;
//        $this->paymentBuilder
//            ->order($order_1_2)->amount($payment_sum_1_2_1)->create();
//
//        // user_2, local, 1 order - success
//        $user_2 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_2 = $this->employeeBuilder
//            ->user($user_2)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//        $sales_plan_sum_2 = 1100;
//        $this->salesPlanBuilder
//            ->employee($employee_2)
//            ->local($sales_plan_sum_2)
//            ->date($targetDate)
//            ->create();
//
//        $order_2_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusSalesDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSalesDone)
//            ->create();
//        $payment_sum_2_1_1 = 150.5000;
//        $this->paymentBuilder
//            ->order($order_2_1)->amount($payment_sum_2_1_1)->create();
//
//        // user_3, local, 2 order - success
//        $user_3 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_3 = $this->employeeBuilder
//            ->user($user_3)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//        $sales_plan_sum_3 = 1100;
//        $this->salesPlanBuilder
//            ->employee($employee_3)
//            ->local($sales_plan_sum_3)
//            ->date($targetDate)
//            ->create();
//
//        $order_3_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_3)
//            ->status($statusSalesDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSalesDone)
//            ->create();
//        $payment_sum_3_1_1 = 30.0000;
//        $this->paymentBuilder
//            ->order($order_3_1)->amount($payment_sum_3_1_1)->create();
//
//        $order_3_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_3)
//            ->status($statusSalesDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSalesDone)
//            ->create();
//        $payment_sum_3_2_1 = 30.0000;
//        $this->paymentBuilder
//            ->order($order_3_2)->amount($payment_sum_3_2_1)->create();
//        $payment_sum_3_2_2 = 710.0000;
//        $this->paymentBuilder
//            ->order($order_3_2)->amount($payment_sum_3_2_2)->create();
//
//        // user_4, local, 0 order - success
//        $user_4 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_4 = $this->employeeBuilder
//            ->user($user_4)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//        $sales_plan_sum_4 = 11;
//        $this->salesPlanBuilder
//            ->employee($employee_4)
//            ->local($sales_plan_sum_4)
//            ->date($targetDate)
//            ->create();
//
//        // user_5, local, 0 order - success, not sales_plan
//        $user_5 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->user($user_5)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//
//        // user_6, long, 1 order - success
//        $user_6 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_6 = $this->employeeBuilder
//            ->user($user_6)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//        $sales_plan_sum_6 = 300;
//        $sales_plan_6 = $this->salesPlanBuilder
//            ->employee($employee_6)
//            ->intrestate($sales_plan_sum_6)
//            ->date($targetDate)
//            ->create();
//
//        $order_6_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_6)
//            ->status($statusSalesDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_6_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSalesDone)
//            ->create();
//        $payment_sum_6_1_1 = 30.0000;
//        $this->paymentBuilder
//            ->order($order_6_1)->amount($payment_sum_6_1_1)->create();
//
//        // user_7, long, 2 order - success
//        $user_7 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_7 = $this->employeeBuilder
//            ->user($user_7)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//        $sales_plan_sum_7 = 300;
//        $this->salesPlanBuilder
//            ->employee($employee_7)
//            ->intrestate($sales_plan_sum_7)
//            ->date($targetDate)
//            ->create();
//
//        $order_7_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_7)
//            ->status($statusSalesDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_7_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSalesDone)
//            ->create();
//        $payment_sum_7_1_1 = 50.0000;
//        $this->paymentBuilder
//            ->order($order_7_1)->amount($payment_sum_7_1_1)->create();
//
//        $order_7_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_7)
//            ->status($statusSalesDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_7_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSalesDone)
//            ->create();
//        $payment_sum_7_2_1 = 1050.0000;
//        $this->paymentBuilder
//            ->order($order_7_2)->amount($payment_sum_7_2_1)->create();
//        $payment_sum_7_2_2 = 350.0000;
//        $this->paymentBuilder
//            ->order($order_7_2)->amount($payment_sum_7_2_2)->create();
//
//        // user_8, long, 1 order - success
//        $user_8 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_8 = $this->employeeBuilder
//            ->user($user_8)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//        $sales_plan_sum_8 = 300;
//        $this->salesPlanBuilder
//            ->employee($employee_8)
//            ->intrestate($sales_plan_sum_8)
//            ->date($targetDate)
//            ->create();
//
//        $order_8_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_8)
//            ->status($statusSalesDone)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_8_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSalesDone)
//            ->create();
//        $payment_sum_8_1_1 = 30.0000;
//        $this->paymentBuilder
//            ->order($order_8_1)->amount($payment_sum_8_1_1)->create();
//
//        // user_9, long, 0 order - success
//        $user_9 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_9 = $this->employeeBuilder
//            ->user($user_9)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//        $sales_plan_sum_9 = 300;
//        $this->salesPlanBuilder
//            ->employee($employee_9)
//            ->intrestate($sales_plan_sum_9)
//            ->date($targetDate)
//            ->create();
//
//        // user_10, not sales_team, 0 order - success
//        $user_10 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->user($user_10)
//            ->sales_team(null)
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $targetDate,
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
////            ->dump()
//            ->assertJsonCount(13, 'data.0')
//            ->assertJsonCount(13, 'data.1')
//            ->assertJsonCount(13, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['user_'.$user_1->id], 2);
//        $this->assertEquals($data[0]['user_'.$user_2->id], 1);
//        $this->assertEquals($data[0]['user_'.$user_3->id], 2);
//        $this->assertEquals($data[0]['user_'.$user_4->id], "");
//        $this->assertEquals($data[0]['user_'.$user_5->id], "");
//        $this->assertEquals($data[0]['user_'.$user_6->id], 1);
//        $this->assertEquals($data[0]['user_'.$user_7->id], 2);
//        $this->assertEquals($data[0]['user_'.$user_8->id], 1);
//        $this->assertEquals($data[0]['user_'.$user_9->id], "");
//
//        $this->assertEquals($data[12]['id'], 13);
//        $this->assertEquals($data[12]['type'], ReportColumnEnum::LeadsSuccessful());
//        $this->assertEquals($data[12]['user_'.$user_1->id], 2);
//        $this->assertEquals($data[12]['user_'.$user_2->id], 1);
//        $this->assertEquals($data[12]['user_'.$user_3->id], 2);
//        $this->assertEquals($data[12]['user_'.$user_4->id], "");
//        $this->assertEquals($data[12]['user_'.$user_5->id], "");
//        $this->assertEquals($data[12]['user_'.$user_6->id], 1);
//        $this->assertEquals($data[12]['user_'.$user_7->id], 2);
//        $this->assertEquals($data[12]['user_'.$user_8->id], 1);
//        $this->assertEquals($data[12]['user_'.$user_9->id], "");
//
//        $successRevenue_1 = $payment_sum_1_1_1 + $payment_sum_1_1_2 + $payment_sum_1_2_1;
//        $successRevenue_2 = $payment_sum_2_1_1;
//        $successRevenue_3 = $payment_sum_3_1_1 + $payment_sum_3_2_1 + $payment_sum_3_2_2;
//        $successRevenue_6 = $payment_sum_6_1_1;
//        $successRevenue_7 = $payment_sum_7_1_1 + $payment_sum_7_2_1 + $payment_sum_7_2_2;
//        $successRevenue_8 = $payment_sum_8_1_1;
//        $this->assertEquals($data[14]['id'], 15);
//        $this->assertEquals($data[14]['type'], ReportColumnEnum::SuccessRevenue());
//        $this->assertEquals($data[14]['user_'.$user_1->id], "$".$successRevenue_1);
//        $this->assertEquals($data[14]['user_'.$user_2->id], "$".to_int($successRevenue_2));
//        $this->assertEquals($data[14]['user_'.$user_3->id], "$".$successRevenue_3);
//        $this->assertEquals($data[14]['user_'.$user_4->id], "");
//        $this->assertEquals($data[14]['user_'.$user_5->id], "");
//        $this->assertEquals($data[14]['user_'.$user_6->id], "$".$successRevenue_6);
//        $this->assertEquals($data[14]['user_'.$user_7->id], "$".$successRevenue_7);
//        $this->assertEquals($data[14]['user_'.$user_8->id], "$".$successRevenue_8);
//        $this->assertEquals($data[14]['user_'.$user_9->id], "");
//
//        $successAOV_1 = $successRevenue_1/2;
//        $successAOV_2 = $successRevenue_2/1;
//        $successAOV_3 = $successRevenue_3/2;
//        $successAOV_6 = $successRevenue_6/1;
//        $successAOV_7 = $successRevenue_7/2;
//        $successAOV_8 = $successRevenue_8/1;
//        $this->assertEquals($data[15]['id'], 16);
//        $this->assertEquals($data[15]['type'], ReportColumnEnum::SuccessAOV());
//        $this->assertEquals($data[15]['user_'.$user_1->id], "$".$successAOV_1);
//        $this->assertEquals($data[15]['user_'.$user_2->id], "$".to_int($successAOV_2));
//        $this->assertEquals($data[15]['user_'.$user_3->id], "$".$successAOV_3);
//        $this->assertEquals($data[15]['user_'.$user_4->id], "");
//        $this->assertEquals($data[15]['user_'.$user_5->id], "");
//        $this->assertEquals($data[15]['user_'.$user_6->id], "$".$successAOV_6);
//        $this->assertEquals($data[15]['user_'.$user_7->id], "$".$successAOV_7);
//        $this->assertEquals($data[15]['user_'.$user_8->id], "$".$successAOV_8);
//        $this->assertEquals($data[15]['user_'.$user_9->id], "");
//
//        $this->assertEquals($data[19]['id'], 20);
//        $this->assertEquals($data[19]['type'], ReportColumnEnum::SalesPlan());
//        $this->assertEquals($data[19]['user_'.$user_1->id], "$".$sales_plan_sum_1);
//        $this->assertEquals($data[19]['user_'.$user_2->id], "$".$sales_plan_sum_2);
//        $this->assertEquals($data[19]['user_'.$user_3->id], "$".$sales_plan_sum_3);
//        $this->assertEquals($data[19]['user_'.$user_4->id], "$".$sales_plan_sum_4);
//        $this->assertEquals($data[19]['user_'.$user_5->id], "");
//        $this->assertEquals($data[19]['user_'.$user_6->id], "$".$sales_plan_sum_6);
//        $this->assertEquals($data[19]['user_'.$user_7->id], "$".$sales_plan_sum_7);
//        $this->assertEquals($data[19]['user_'.$user_8->id], "$".$sales_plan_sum_8);
//        $this->assertEquals($data[19]['user_'.$user_9->id], "$".$sales_plan_sum_9);
//
//        $salesFactCR_1 = round(($successRevenue_1/$sales_plan_sum_1)*100, 2);
//        $salesFactCR_2 = round(($successRevenue_2/$sales_plan_sum_2)*100, 2);
//        $salesFactCR_3 = round(($successRevenue_3/$sales_plan_sum_3)*100, 2);
//        $salesFactCR_6 = round(($successRevenue_6/$sales_plan_sum_6)*100, 2);
//        $salesFactCR_7 = round(($successRevenue_7/$sales_plan_sum_7)*100, 2);
//        $salesFactCR_8 = round(($successRevenue_8/$sales_plan_sum_8)*100, 2);
//        $this->assertEquals($data[21]['id'], 22);
//        $this->assertEquals($data[21]['type'], ReportColumnEnum::SalesFactCR());
//        $this->assertEquals($data[21]['user_'.$user_1->id], $salesFactCR_1."%");
//        $this->assertEquals($data[21]['user_'.$user_2->id], $salesFactCR_2."%");
//        $this->assertEquals($data[21]['user_'.$user_3->id], $salesFactCR_3."%");
//        $this->assertEquals($data[21]['user_'.$user_4->id], "");
//        $this->assertEquals($data[21]['user_'.$user_5->id], "");
//        $this->assertEquals($data[21]['user_'.$user_6->id], $salesFactCR_6."%");
//        $this->assertEquals($data[21]['user_'.$user_7->id], $salesFactCR_7."%");
//        $this->assertEquals($data[21]['user_'.$user_8->id], $salesFactCR_8."%");
//        $this->assertEquals($data[21]['user_'.$user_9->id], "");
//
//        $this->assertEquals($data[22]['id'], 23);
//        $this->assertEquals($data[22]['type'], ReportColumnEnum::SalesRank());
//        $this->assertEquals($data[22]['user_'.$user_1->id], 2);
//        $this->assertEquals($data[22]['user_'.$user_2->id], 3);
//        $this->assertEquals($data[22]['user_'.$user_3->id], 1);
//        $this->assertEquals($data[22]['user_'.$user_4->id], "");
//        $this->assertEquals($data[22]['user_'.$user_5->id], "");
//        $this->assertEquals($data[22]['user_'.$user_6->id], 2);
//        $this->assertEquals($data[22]['user_'.$user_7->id], 1);
//        $this->assertEquals($data[22]['user_'.$user_8->id], 2);
//        $this->assertEquals($data[22]['user_'.$user_9->id], "");
//    }
//
//    // проверяем расчет ранга (efficiency rank) для сотрудников local/long
//    // 10 сотрудника
//    /** @test */
//    public function check_orders_efficiency_rank_for_local_and_long_employee()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//        // date
//        $now = CarbonImmutable::now();
//        $targetDate = $now->subMonth()->format('Y-m');
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder->asNewLead()->create();
//        $statusBooked  = $this->orderStatusBuilder->asBooked()->create();
//        $statusDuplicate  = $this->orderStatusBuilder->asDuplicate()->create();
//        $statusSuccess  = $this->orderStatusBuilder->asSuccess()->create();
//        $statusLost  = $this->orderStatusBuilder->asLost()->create();
//
//        // tags
//        $tagBadZip = $this->tagBuilder->asBadZip()->create();
//        $tagCantService = $this->tagBuilder->asCantService()->create();
//
//        // users/employees/orders
//        // user_1, local, orders: booked - 2, badZip - 3, cantService - 1, duplicate - 1, success -2
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_1 = $this->employeeBuilder
//            ->user($user_1)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//
//        $order_1_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_1_1 = 100;
//        $this->estimateCalculatedBuilder
//            ->order($order_1_1)
//            ->title('total')
//            ->value("$".$booked_sum_1_1)
//            ->create();
//
//        $order_1_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_1_2 = 300;
//        $this->estimateCalculatedBuilder
//            ->order($order_1_2)
//            ->title('total')
//            ->value("$".$booked_sum_1_2)
//            ->create();
//
//        $order_1_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->tags($tagBadZip)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//        $order_1_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->tags($tagBadZip)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//        $order_1_5 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->tags($tagBadZip)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_5)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//
//        $order_1_6 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_6)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//        $order_1_7 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_7)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//
//        $order_1_8 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_8)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $payment_sum_1_8_1 = 50.0000;
//        $this->paymentBuilder
//            ->order($order_1_8)->amount($payment_sum_1_8_1)->create();
//
//        $order_1_9 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1_9)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $payment_sum_1_9_1 = 150.0000;
//        $this->paymentBuilder
//            ->order($order_1_8)->amount($payment_sum_1_9_1)->create();
//
//        // user_2, local, orders: booked - 1, badZip - 2, cantService - 1, duplicate - 0, success - 0
//        $user_2 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_2 = $this->employeeBuilder
//            ->user($user_2)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//
//        $order_2_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_2_1 = 1900;
//        $this->estimateCalculatedBuilder
//            ->order($order_2_1)
//            ->title('total')
//            ->value("$".$booked_sum_2_1)
//            ->create();
//
//        $order_2_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusLost)
//            ->tags($tagBadZip)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//        $order_2_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusLost)
//            ->tags($tagBadZip)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//        $order_2_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusLost)
//            ->tags($tagCantService)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//
//        // user_3, local, orders: booked - 1, badZip - 0, cantService - 0, duplicate - 2, success - 1
//        $user_3 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_3 = $this->employeeBuilder
//            ->user($user_3)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//
//        $order_3_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_3)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_3_1 = 40;
//        $this->estimateCalculatedBuilder
//            ->order($order_3_1)
//            ->title('total')
//            ->value("$".$booked_sum_3_1)
//            ->create();
//
//        $order_3_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_3)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//        $order_3_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_3)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//
//        $order_3_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_3)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $payment_sum_3_4_1 = 170.0000;
//        $this->paymentBuilder
//            ->order($order_3_4)->amount($payment_sum_3_4_1)->create();
//
//        // user_4, local, orders: booked - 1, badZip - 0, cantService - 0, duplicate - 0, success - 0
//        $user_4 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_4 = $this->employeeBuilder
//            ->user($user_4)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//
//        $order_4_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_4)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_4_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_4_1 = 470;
//        $this->estimateCalculatedBuilder
//            ->order($order_4_1)
//            ->title('total')
//            ->value("$".$booked_sum_4_1)
//            ->create();
//
//        // user_5, long, orders: booked - 1, badZip - 2, cantService - 0, duplicate - 2, success - 2
//        $user_5 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_5 = $this->employeeBuilder
//            ->user($user_5)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//
//        $order_5_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_5)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_5_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_5_1 = 170;
//        $this->estimateCalculatedBuilder
//            ->order($order_5_1)
//            ->title('total')
//            ->value("$".$booked_sum_5_1)
//            ->create();
//
//        $order_5_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_5)
//            ->status($statusLost)
//            ->tags($tagBadZip)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_5_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//        $order_5_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_5)
//            ->status($statusLost)
//            ->tags($tagBadZip)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_5_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//
//        $order_5_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_5)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_5_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//        $order_5_5 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_5)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_5_5)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//
//        $order_5_6 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_5)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_5_6)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $payment_sum_5_6_1 = 70.0000;
//        $this->paymentBuilder
//            ->order($order_5_6)->amount($payment_sum_5_6_1)->create();
//
//        $order_5_7 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_5)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_5_7)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $payment_sum_5_7_1 = 60.0000;
//        $this->paymentBuilder
//            ->order($order_5_7)->amount($payment_sum_5_7_1)->create();
//        $payment_sum_5_7_2 = 160.0000;
//        $this->paymentBuilder
//            ->order($order_5_7)->amount($payment_sum_5_7_2)->create();
//
//        // user_6, long, orders: booked - 1, badZip - 0, cantService - 0, duplicate - 0, success - 3
//        $user_6 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_6 = $this->employeeBuilder
//            ->user($user_6)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//
//        $order_6_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_6)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_6_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_6_1 = 80;
//        $this->estimateCalculatedBuilder
//            ->order($order_6_1)
//            ->title('total')
//            ->value("$".$booked_sum_6_1)
//            ->create();
//
//        $order_6_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_6)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_6_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $payment_sum_6_2_1 = 140.0000;
//        $this->paymentBuilder
//            ->order($order_6_2)->amount($payment_sum_6_2_1)->create();
//
//        $order_6_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_6)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_6_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $payment_sum_6_3_1 = 145.0000;
//        $this->paymentBuilder
//            ->order($order_6_3)->amount($payment_sum_6_3_1)->create();
//
//        $order_6_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_6)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_6_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $payment_sum_6_4_1 = 15.0000;
//        $this->paymentBuilder
//            ->order($order_6_4)->amount($payment_sum_6_4_1)->create();
//
//        // user_7, long, orders: booked - 3, badZip - 0, cantService - 0, duplicate - 2, success - 0
//        $user_7 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_7 = $this->employeeBuilder
//            ->user($user_7)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//
//        $order_7_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_7)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_7_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_7_1 = 80;
//        $this->estimateCalculatedBuilder
//            ->order($order_7_1)
//            ->title('total')
//            ->value("$".$booked_sum_7_1)
//            ->create();
//
//        $order_7_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_7)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_7_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_7_2 = 180;
//        $this->estimateCalculatedBuilder
//            ->order($order_7_2)
//            ->title('total')
//            ->value("$".$booked_sum_7_2)
//            ->create();
//
//        $order_7_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_7)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_7_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $booked_sum_7_3 = 100;
//        $this->estimateCalculatedBuilder
//            ->order($order_7_3)
//            ->title('total')
//            ->value("$".$booked_sum_7_3)
//            ->create();
//
//        $order_7_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_7)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_7_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//
//        $order_7_5 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_7)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_7_5)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//
//        // user_8, long, orders: booked - 0, badZip - 0, cantService - 0, duplicate - 0, success - 0
//        $user_8 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $employee_8 = $this->employeeBuilder
//            ->user($user_8)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $targetDate,
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
////            ->dump()
//            ->assertJsonCount(12, 'data.0')
//            ->assertJsonCount(12, 'data.1')
//            ->assertJsonCount(12, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['user_'.$user_1->id], 9);
//        $this->assertEquals($data[0]['user_'.$user_2->id], 4);
//        $this->assertEquals($data[0]['user_'.$user_3->id], 4);
//        $this->assertEquals($data[0]['user_'.$user_4->id], 1);
//        $this->assertEquals($data[0]['user_'.$user_5->id],  7);
//        $this->assertEquals($data[0]['user_'.$user_6->id], 4);
//        $this->assertEquals($data[0]['user_'.$user_7->id], 5);
//        $this->assertEquals($data[0]['user_'.$user_8->id], "");
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['type'], ReportColumnEnum::LeadsLost());
//        $this->assertEquals($data[1]['user_'.$user_1->id], 3);
//        $this->assertEquals($data[1]['user_'.$user_2->id], 3);
//        $this->assertEquals($data[1]['user_'.$user_3->id], "");
//        $this->assertEquals($data[1]['user_'.$user_4->id], "");
//        $this->assertEquals($data[1]['user_'.$user_5->id],  2);
//        $this->assertEquals($data[1]['user_'.$user_6->id], "");
//        $this->assertEquals($data[1]['user_'.$user_7->id], "");
//        $this->assertEquals($data[1]['user_'.$user_8->id], "");
//
//        $this->assertEquals($data[6]['id'], 7);
//        $this->assertEquals($data[6]['type'], ReportColumnEnum::LeadsBooked());
//        $this->assertEquals($data[6]['user_'.$user_1->id], 2);
//        $this->assertEquals($data[6]['user_'.$user_2->id], 1);
//        $this->assertEquals($data[6]['user_'.$user_3->id], 1);
//        $this->assertEquals($data[6]['user_'.$user_4->id], 1);
//        $this->assertEquals($data[6]['user_'.$user_5->id],  1);
//        $this->assertEquals($data[6]['user_'.$user_6->id], 1);
//        $this->assertEquals($data[6]['user_'.$user_7->id], 3);
//        $this->assertEquals($data[6]['user_'.$user_8->id], "");
//
//        $this->assertEquals($data[16]['id'], 17);
//        $this->assertEquals($data[16]['type'], ReportColumnEnum::LeadsDuplicate());
//        $this->assertEquals($data[16]['user_'.$user_1->id], 2);
//        $this->assertEquals($data[16]['user_'.$user_2->id], "");
//        $this->assertEquals($data[16]['user_'.$user_3->id], 2);
//        $this->assertEquals($data[16]['user_'.$user_4->id], "");
//        $this->assertEquals($data[16]['user_'.$user_5->id],  2);
//        $this->assertEquals($data[16]['user_'.$user_6->id], "");
//        $this->assertEquals($data[16]['user_'.$user_7->id], 2);
//        $this->assertEquals($data[16]['user_'.$user_8->id], "");
//
//        $this->assertEquals($data[17]['id'], 18);
//        $this->assertEquals($data[17]['type'], ReportColumnEnum::LeadsBadZip());
//        $this->assertEquals($data[17]['user_'.$user_1->id], 3);
//        $this->assertEquals($data[17]['user_'.$user_2->id], 2);
//        $this->assertEquals($data[17]['user_'.$user_3->id], "");
//        $this->assertEquals($data[17]['user_'.$user_4->id], "");
//        $this->assertEquals($data[17]['user_'.$user_5->id],  2);
//        $this->assertEquals($data[17]['user_'.$user_6->id], "");
//        $this->assertEquals($data[17]['user_'.$user_7->id], "");
//        $this->assertEquals($data[17]['user_'.$user_8->id], "");
//
//        $this->assertEquals($data[18]['id'], 19);
//        $this->assertEquals($data[18]['type'], ReportColumnEnum::LeadsCantService());
//        $this->assertEquals($data[18]['user_'.$user_1->id], "");
//        $this->assertEquals($data[18]['user_'.$user_2->id], 1);
//        $this->assertEquals($data[18]['user_'.$user_3->id], "");
//        $this->assertEquals($data[18]['user_'.$user_4->id], "");
//        $this->assertEquals($data[18]['user_'.$user_5->id],  "");
//        $this->assertEquals($data[18]['user_'.$user_6->id], "");
//        $this->assertEquals($data[18]['user_'.$user_7->id], "");
//        $this->assertEquals($data[18]['user_'.$user_8->id], "");
//
//        $this->assertEquals($data[8]['id'], 9);
//        $this->assertEquals($data[8]['type'], ReportColumnEnum::LeadsBookedSum());
//        $leadBookedSum_1 = $booked_sum_1_1 + $booked_sum_1_2;
//        $leadBookedSum_2 = $booked_sum_2_1;
//        $leadBookedSum_3 = $booked_sum_3_1;
//        $leadBookedSum_4 = $booked_sum_4_1;
//        $leadBookedSum_5 = $booked_sum_5_1;
//        $leadBookedSum_6 = $booked_sum_6_1;
//        $leadBookedSum_7 = $booked_sum_7_1 + $booked_sum_7_2 + $booked_sum_7_3;
//        $this->assertEquals($data[8]['user_'.$user_1->id], "$".$leadBookedSum_1);
//        $this->assertEquals($data[8]['user_'.$user_2->id], "$".$leadBookedSum_2);
//        $this->assertEquals($data[8]['user_'.$user_3->id], "$".$leadBookedSum_3);
//        $this->assertEquals($data[8]['user_'.$user_4->id], "$".$leadBookedSum_4);
//        $this->assertEquals($data[8]['user_'.$user_5->id],  "$".$leadBookedSum_5);
//        $this->assertEquals($data[8]['user_'.$user_6->id], "$".$leadBookedSum_6);
//        $this->assertEquals($data[8]['user_'.$user_7->id], "$".$leadBookedSum_7);
//        $this->assertEquals($data[8]['user_'.$user_8->id], "");
//
//        $this->assertEquals($data[24]['id'], 25);
//        $this->assertEquals($data[24]['type'], ReportColumnEnum::ConversionFact());
////        ($bookedCount/($leadCount - $duplicateCount - $zipCodeCount - $cantServiceCount))*100
//        $conversionFact_1 = (2/(9-2-3-0))*100;
//        $conversionFact_2 = (1/(4-0-2-1))*100;
//        $conversionFact_3 = (1/(4-2-0-0))*100;
//        $conversionFact_4 = (1/(1-0-0-0))*100;
//        $conversionFact_5 = round((1/(7-2-2-0))*100, 2);
//        $conversionFact_6 = (1/(4-0-0-0))*100;
//        $conversionFact_7 = (3/(5-2-0-0))*100;
//        $this->assertEquals($data[24]['user_'.$user_1->id], $conversionFact_1.'%');
//        $this->assertEquals($data[24]['user_'.$user_2->id], $conversionFact_2.'%');
//        $this->assertEquals($data[24]['user_'.$user_3->id], $conversionFact_3.'%');
//        $this->assertEquals($data[24]['user_'.$user_4->id], $conversionFact_4.'%');
//        $this->assertEquals($data[24]['user_'.$user_5->id], $conversionFact_5.'%');
//        $this->assertEquals($data[24]['user_'.$user_6->id], $conversionFact_6.'%');
//        $this->assertEquals($data[24]['user_'.$user_7->id], $conversionFact_7.'%');
//        $this->assertEquals($data[24]['user_'.$user_8->id],"");
//
//        $this->assertEquals($data[25]['id'], 26);
//        $this->assertEquals($data[25]['type'], ReportColumnEnum::EfficiencyRank());
//        $this->assertEquals($data[25]['user_'.$user_1->id], 2);
//        $this->assertEquals($data[25]['user_'.$user_2->id], 1);
//        $this->assertEquals($data[25]['user_'.$user_3->id], 2);
//        $this->assertEquals($data[25]['user_'.$user_4->id], 1);
//
//        $this->assertEquals($data[25]['user_'.$user_5->id], 2);
//        $this->assertEquals($data[25]['user_'.$user_6->id], 3);
//        $this->assertEquals($data[25]['user_'.$user_7->id], 1);
//        $this->assertEquals($data[25]['user_'.$user_8->id],"");
//    }
//
//    // проверяем заказы со статусом lost
//    // также используется фильтр по sales_team со значением - local_long
//    // два сотрудника
//    // не попадает в выборку сотрудник с другим значением и его заказы
//    /** @test */
//    public function check_orders_by_status_as_lost_filter_by_local_team()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//        // date
//        $now = CarbonImmutable::now();
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // users/employees
//        // not, unsuitable sales_team
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->user($user_1)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//
//        $user_2 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->user($user_2)
//            ->sales_team(SalesTeamEnum::Local_long)
//            ->create();
//
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder
//            ->asNewLead()
//            ->create();
//        $statusLost  = $this->orderStatusBuilder
//            ->asLost()
//            ->create();
//
//        // orders/orderStatusHistory
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusNewLead)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_2)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $now->subMonth()->format('Y-m'),
//                'period-type' => 'by_creation',
//                'sales_team' => SalesTeamEnum::Local_long(),
//            ]
//        ];
//
//        $res = $this->post(route('reports.sales.datatable'), $filter)
//            ->assertJsonCount(5, 'data.0')
//            ->assertJsonCount(5, 'data.1')
//            ->assertJsonCount(5, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['user_'.$user_2->id], 2);
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['user_'.$user_2->id], 2);
//
//        $this->assertEquals($data[2]['id'], 3);
//        $this->assertEquals($data[2]['user_'.$user_2->id], "100%");
//
//        $this->assertEquals($data[3]['id'], 4);
//        $this->assertEquals($data[3]['user_'.$user_2->id], "");
//
//        $this->assertEquals($data[4]['id'], 5);
//        $this->assertEquals($data[4]['user_'.$user_2->id], "0%");
//
//        $this->assertEquals($data[7]['id'], 8);
//        $this->assertEquals($data[7]['user_'.$user_2->id], "0%");
//
//        $this->assertEquals($data[13]['id'], 14);
//        $this->assertEquals($data[13]['user_'.$user_2->id], "0%");
//    }
//
//    // проверяем заказ со статусом duplicate, также есть проверка на заказ со
//    // статусом lost, один сотрудник
//    /** @test */
//    public function check_orders_by_status_as_duplicate()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//
//        // date
//        $now = CarbonImmutable::now();
//
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // user/employee
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->user($user_1)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder
//            ->asNewLead()
//            ->create();
//        $statusDuplicate  = $this->orderStatusBuilder
//            ->asDuplicate()
//            ->create();
//        $statusLost  = $this->orderStatusBuilder
//            ->asLost()
//            ->create();
//
//        // orders/orderStatusHistory
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusLost)
//            ->create();
//
//        // filter
//        $filter = [
//            'filter' => [
//                'date' => $now->subMonth()->format('Y-m'),
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
//            ->assertJsonCount(5, 'data.0')
//            ->assertJsonCount(5, 'data.1')
//            ->assertJsonCount(5, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        // check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['user_'.$user_1->id], 3);
//        $this->assertEquals($data[0]['user_0'], "");
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['user_'.$user_1->id], 1);
//        $this->assertEquals($data[1]['user_0'], "");
//
//        $this->assertEquals($data[2]['id'], 3);
//        $this->assertEquals($data[2]['user_'.$user_1->id], "33.33%");
//        $this->assertEquals($data[2]['user_0'], "");
//
//        // duplicate
//        $this->assertEquals($data[16]['id'], 17);
//        $this->assertEquals($data[16]['user_'.$user_1->id], 2);
//        $this->assertEquals($data[16]['user_0'], "");
//        $this->assertEquals($data[16]['type'], ReportColumnEnum::LeadsDuplicate());
//    }
//
//    // проверяем заказы с тегом bad_zip (и статусом lost)
//    // один сотрудник
//    /** @test */
//    public function check_orders_by_tag_bad_zip()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//
//        // date
//        $now = CarbonImmutable::now();
//
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // user/employee
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_1)
//            ->create();
//
//        // statuses
//        $statusLost  = $this->orderStatusBuilder->asLost()->create();
//        $statusBooked  = $this->orderStatusBuilder->asBooked()->create();
//
//        // tags
//        $tagBadZip = $this->tagBuilder->asBadZip()->create();
//        $tagNoAnswer = $this->tagBuilder->asNoAnswer()->create();
//        $tagCantService = $this->tagBuilder->asCantService()->create();
//
//        // orders/
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagBadZip, $tagNoAnswer)
//            ->create();
//
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagBadZip)
//            ->create();
//
//        // not check, has tag CantService
//        $order_2_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagBadZip, $tagCantService)
//            ->create();
//
//        // not check, unsuitable tag
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagNoAnswer)
//            ->create();
//
//        // not check, not have tag
//        $order_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//
//        // not check, not have status Lost
//        $order_5 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->tags($tagBadZip)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//
//        // filter
//        $filter = [
//            'filter' => [
//                'date' => $now->subMonth()->format('Y-m'),
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
////            ->dump()
//        ;
//
//        $data = $res->json('data');
//
//        // check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['user_'.$user_1->id], "");
//        $this->assertEquals($data[0]['user_0'], "");
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['type'], ReportColumnEnum::LeadsLost());
//        $this->assertEquals($data[1]['user_'.$user_1->id], 5);
//        $this->assertEquals($data[1]['user_0'], "");
//
//        $this->assertEquals($data[17]['id'], 18);
//        $this->assertEquals($data[17]['type'], ReportColumnEnum::LeadsBadZip());
//        $this->assertEquals($data[17]['user_'.$user_1->id], 2);
//        $this->assertEquals($data[17]['user_0'], "");
//
//        $this->assertEquals($data[18]['id'], 19);
//        $this->assertEquals($data[18]['type'], ReportColumnEnum::LeadsCantService());
//        $this->assertEquals($data[18]['user_'.$user_1->id], 1);
//        $this->assertEquals($data[18]['user_0'], "");
//    }
//
//    // проверяем заказ с тегом cant_service (и статусом lost), также есть заказ которые
//    // включает теги - cant_service и bad_zip
//    // один сотрудник
//    /** @test */
//    public function check_orders_by_tag_cant_service()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//
//        // date
//        $now = CarbonImmutable::now();
//
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // user/employee
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_1)
//            ->create();
//
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder->asNewLead()->create();
//        $statusLost  = $this->orderStatusBuilder->asLost()->create();
//
//        // tags
//        $tagBadZip = $this->tagBuilder->asBadZip()->create();
//        $tagNoAnswer = $this->tagBuilder->asNoAnswer()->create();
//        $tagCantService = $this->tagBuilder->asCantService()->create();
//
//        // orders/
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagCantService, $tagNoAnswer)
//            ->create();
//
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagCantService)
//            ->create();
//
//        $order_2_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagCantService, $tagBadZip)
//            ->create();
//
//        $order_2_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagBadZip)
//            ->create();
//
//        // not check, unsuitable tag
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagNoAnswer)
//            ->create();
//
//        // not check, not have tag
//        $order_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//
//        // not check, not have status Lost
//        $order_5 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusNewLead)
//            ->tags($tagBadZip)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//
//        // filter
//        $filter = [
//            'filter' => [
//                'date' => $now->subMonth()->format('Y-m'),
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
////            ->dump()
//        ;
//
//        $data = $res->json('data');
//
//        // check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['user_'.$user_1->id], 1);
//        $this->assertEquals($data[0]['user_0'], "");
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['type'], ReportColumnEnum::LeadsLost());
//        $this->assertEquals($data[1]['user_'.$user_1->id], 6);
//        $this->assertEquals($data[1]['user_0'], "");
//
//        $this->assertEquals($data[17]['id'], 18);
//        $this->assertEquals($data[17]['type'], ReportColumnEnum::LeadsBadZip());
//        $this->assertEquals($data[17]['user_'.$user_1->id], 1);
//        $this->assertEquals($data[17]['user_0'], "");
//
//        $this->assertEquals($data[18]['id'], 19);
//        $this->assertEquals($data[18]['type'], ReportColumnEnum::LeadsCantService());
//        $this->assertEquals($data[18]['user_'.$user_1->id], 3);
//        $this->assertEquals($data[18]['user_0'], "");
//    }
//
//    // проверяем заказ со статусом booked и подсчет по данным заказам,
//    // а также данные по нулевому пользователю (т.е. заказы без менеджера)
//    // один сотрудник,
//    /** @test */
//    public function check_orders_by_status_as_booked_with_calc_and_user_0()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//        // date
//        $now = CarbonImmutable::now();
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // user/employee
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_1)
//            ->create();
//
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder
//            ->asNewLead()
//            ->create();
//        $statusBooked  = $this->orderStatusBuilder
//            ->asBooked()
//            ->create();
//
//        // orders/orderStatusHistory/estimateCalculated
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_1)
//            ->title('total')
//            ->value("$100")
//            ->create();
//
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_2)
//            ->title('total')
//            ->value("$200")
//            ->create();
//
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_3)
//            ->title('total')
//            ->value("$8,050.95")
//            ->create();
//
//        $order_4 = $this->orderBuilder
//            ->division($division)
//            ->manager(null)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_4)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_4)
//            ->title('total')
//            ->value("$50.95")
//            ->create();
//
//        $order_5 = $this->orderBuilder
//            ->division($division)
//            ->manager(null)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_5)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_5)
//            ->title('total')
//            ->value("$100")
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $now->subMonth()->format('Y-m'),
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        $res = $this->post(route('reports.sales.datatable'), $filter)
////            ->dump()
//            ->assertJsonCount(5, 'data.0')
//            ->assertJsonCount(5, 'data.1')
//            ->assertJsonCount(5, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['user_'.$user_1->id], 3);
//        $this->assertEquals($data[0]['user_0'], 2);
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['user_'.$user_1->id], "");
//        $this->assertEquals($data[1]['user_0'], "");
//
//        $this->assertEquals($data[2]['id'], 3);
//        $this->assertEquals($data[2]['user_'.$user_1->id], "0%");
//        $this->assertEquals($data[2]['user_0'], "0%");
//
//        $this->assertEquals($data[4]['id'], 5);
//        $this->assertEquals($data[4]['user_'.$user_1->id], "0%");
//        $this->assertEquals($data[4]['user_0'], "0%");
//
//        $this->assertEquals($data[6]['id'], 7);
//        $this->assertEquals($data[6]['user_'.$user_1->id], 3);
//        $this->assertEquals($data[6]['user_0'], 2);
//
//        $this->assertEquals($data[7]['id'], 8);
//        $this->assertEquals($data[7]['user_'.$user_1->id], "100%");
//        $this->assertEquals($data[7]['user_0'], "100%");
//
//        $this->assertEquals($data[8]['id'], 9);
//        $this->assertEquals($data[8]['user_'.$user_1->id], "$8351");
//        $this->assertEquals($data[8]['user_0'], "$151");
//
//        $this->assertEquals($data[13]['id'], 14);
//        $this->assertEquals($data[13]['user_'.$user_1->id], "0%");
//        $this->assertEquals($data[13]['user_0'], "0%");
//    }
//
//    // проверяем заказ со статусом booked и подсчет по данным заказам,
//    // а также данные по нулевому пользователю (т.е. заказы без менеджера)
//    // один сотрудник,
//    /** @test */
//    public function check_orders_by_status_as_booked_from_calculated()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//        // date
//        $now = CarbonImmutable::now();
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // user/employee
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_1)
//            ->create();
//
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder
//            ->asNewLead()
//            ->create();
//        $statusBooked  = $this->orderStatusBuilder
//            ->asBooked()
//            ->create();
//        $statusCalculatedDone = $this->orderStatusBuilder
//            ->asDone()
//            ->create();
//        $statusSuccess = $this->orderStatusBuilder
//            ->asSuccess()
//            ->create();
//
//        // orders/orderStatusHistory/estimateCalculated
//        /** @var $order_1 Order */
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusCalculatedDone)
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusCalculatedDone)
//            ->new_status($statusBooked)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_1)
//            ->title('total')
//            ->value("$100")
//            ->create();
//
//        /** @var $order_2 Order */
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusCalculatedDone)
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusCalculatedDone)
//            ->new_status($statusBooked)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_2)
//            ->title('total')
//            ->value("$130")
//            ->create();
//
//        // not
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_3)
//            ->title('total')
//            ->value("$200")
//            ->create();
//
//        // not
//        $order_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusCalculatedDone)
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusCalculatedDone)
//            ->new_status($statusSuccess)
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusSuccess)
//            ->new_status($statusBooked)
//            ->create();
//        $this->estimateCalculatedBuilder
//            ->order($order_3)
//            ->title('total')
//            ->value("$200")
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $now->subMonth()->format('Y-m'),
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        $res = $this->post(route('reports.sales.datatable'), $filter)
//            ->assertJsonCount(5, 'data.0')
//            ->assertJsonCount(5, 'data.1')
//            ->assertJsonCount(5, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['user_'.$user_1->id], 3);
//
//        $this->assertEquals($data[9]['id'], 10);
//        $this->assertEquals($data[9]['type'], ReportColumnEnum::LeadsBookedFromCalculated());
//        $this->assertEquals($data[9]['user_'.$user_1->id], 2);
//
//        $this->assertEquals($data[10]['id'], 11);
//        $this->assertEquals($data[10]['type'], ReportColumnEnum::LeadsBookedFromCalculatedCR());
//        $this->assertEquals($data[10]['user_'.$user_1->id], round(100 * 2 / 3, 2) .'%');
//
//        $this->assertEquals($data[11]['id'], 12);
//        $this->assertEquals($data[11]['type'], ReportColumnEnum::LeadsBookedFromCalculatedSum());
//        $this->assertEquals($data[11]['user_'.$user_1->id], "$230");
//
//
//    }
//
//    // проверяем расчет по conversion_fact
//    // один сотрудник,
//    /** @test */
//    public function check_orders_by_conversion_fact()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//        // date
//        $now = CarbonImmutable::now();
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // user/employee
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_1)
//            ->create();
//
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder->asNewLead()->create();
//        $statusBooked  = $this->orderStatusBuilder->asBooked()->create();
//        $statusDuplicate  = $this->orderStatusBuilder->asDuplicate()->create();
//        $statusSuccess  = $this->orderStatusBuilder->asSuccess()->create();
//        $statusLost  = $this->orderStatusBuilder->asLost()->create();
//
//        // tags
//        $tagBadZip = $this->tagBuilder->asBadZip()->create();
//
//        // orders/orderStatusHistory/estimateCalculated
//        /** @var $order_1 Order */
//        // booked
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        // booked
//        $order_2 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusBooked)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_2)
//            ->prev_status($statusNewLead)
//            ->new_status($statusBooked)
//            ->create();
//        // duplicate
//        $order_3 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_3)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//        // badZip
//        $order_4 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagBadZip)
//            ->create();
//        $order_5 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagBadZip)
//            ->create();
//        $order_6 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusLost)
//            ->created_at($now->subMonth()->subDays(2))
//            ->tags($tagBadZip)
//            ->create();
//        // new lead
//        $order_7 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusNewLead)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $order_8 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusNewLead)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $order_9 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusNewLead)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        // success
//        $order_10 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_10)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//        $order_11 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusSuccess)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_11)
//            ->prev_status($statusNewLead)
//            ->new_status($statusSuccess)
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $now->subMonth()->format('Y-m'),
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        $res = $this->post(route('reports.sales.datatable'), $filter)
////            ->dump()
//            ->assertJsonCount(5, 'data.0')
//            ->assertJsonCount(5, 'data.1')
//            ->assertJsonCount(5, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['user_'.$user_1->id], 8);
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['type'], ReportColumnEnum::LeadsLost());
//        $this->assertEquals($data[1]['user_'.$user_1->id], 3);
//
//        $this->assertEquals($data[2]['id'], 3);
//        $this->assertEquals($data[2]['type'], ReportColumnEnum::LeadsLostCR());
//        $this->assertEquals($data[2]['user_'.$user_1->id], "37.5%");
//
//        $this->assertEquals($data[6]['id'], 7);
//        $this->assertEquals($data[6]['type'], ReportColumnEnum::LeadsBooked());
//        $this->assertEquals($data[6]['user_'.$user_1->id], 2);
//
//        $this->assertEquals($data[7]['id'], 8);
//        $this->assertEquals($data[7]['type'], ReportColumnEnum::LeadsBookedCR());
//        $this->assertEquals($data[7]['user_'.$user_1->id], "25%");
//
//        $this->assertEquals($data[12]['id'], 13);
//        $this->assertEquals($data[12]['type'], ReportColumnEnum::LeadsSuccessful());
//        $this->assertEquals($data[12]['user_'.$user_1->id], 2);
//
//        $this->assertEquals($data[13]['id'], 14);
//        $this->assertEquals($data[13]['type'], ReportColumnEnum::LeadsSuccessfulCR());
//        $this->assertEquals($data[13]['user_'.$user_1->id], "25%");
//
//        $this->assertEquals($data[16]['id'], 17);
//        $this->assertEquals($data[16]['type'], ReportColumnEnum::LeadsDuplicate());
//        $this->assertEquals($data[16]['user_'.$user_1->id], 1);
//
//        $this->assertEquals($data[17]['id'], 18);
//        $this->assertEquals($data[17]['type'], ReportColumnEnum::LeadsBadZip());
//        $this->assertEquals($data[17]['user_'.$user_1->id], 3);
//
//        $this->assertEquals($data[18]['id'], 19);
//        $this->assertEquals($data[18]['type'], ReportColumnEnum::LeadsCantService());
//        $this->assertEquals($data[18]['user_'.$user_1->id], "");
//
//        $conversionFact = round(
//            (2/(8-3-0-1))*100,
//            2
//        );
//
//        $this->assertEquals($data[24]['id'], 25);
//        $this->assertEquals($data[24]['type'], ReportColumnEnum::ConversionFact());
//        $this->assertEquals($data[24]['user_'.$user_1->id], $conversionFact."%");
//    }
//
//    // проверяем расчет по conversion_fact когда в делители 0
//    // один сотрудник,
//    /** @test */
//    public function check_orders_by_conversion_fact_as_zero()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//        // date
//        $now = CarbonImmutable::now();
//        // role
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//
//        // user/employee
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_1)
//            ->create();
//
//        // statuses
//        $statusNewLead  = $this->orderStatusBuilder->asNewLead()->create();
//        $statusDuplicate  = $this->orderStatusBuilder->asDuplicate()->create();
//
//        /** @var $order_1 Order */
//        // booked
//        $order_1 = $this->orderBuilder
//            ->division($division)
//            ->manager($user_1)
//            ->status($statusDuplicate)
//            ->created_at($now->subMonth()->subDays(2))
//            ->create();
//        $this->orderStatusHistoryBuilder
//            ->order_id($order_1)
//            ->prev_status($statusNewLead)
//            ->new_status($statusDuplicate)
//            ->create();
//
//        $filter = [
//            'filter' => [
//                'date' => $now->subMonth()->format('Y-m'),
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        $res = $this->post(route('reports.sales.datatable'), $filter)
//            ->assertJsonCount(5, 'data.0')
//            ->assertJsonCount(5, 'data.1')
//            ->assertJsonCount(5, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['user_'.$user_1->id], 1);
//
//        $this->assertEquals($data[16]['id'], 17);
//        $this->assertEquals($data[16]['type'], ReportColumnEnum::LeadsDuplicate());
//        $this->assertEquals($data[16]['user_'.$user_1->id], 1);
//
//        $this->assertEquals($data[17]['id'], 18);
//        $this->assertEquals($data[17]['type'], ReportColumnEnum::LeadsBadZip());
//        $this->assertEquals($data[17]['user_'.$user_1->id], "");
//
//        $this->assertEquals($data[18]['id'], 19);
//        $this->assertEquals($data[18]['type'], ReportColumnEnum::LeadsCantService());
//        $this->assertEquals($data[18]['user_'.$user_1->id], "");
//
//        $this->assertEquals($data[24]['id'], 25);
//        $this->assertEquals($data[24]['type'], ReportColumnEnum::ConversionFact());
//        $this->assertEquals($data[24]['user_'.$user_1->id], "0%");
//    }
//
//    // проверка всех полей ответа (таблицы) при отсутствии значений,
//    // в ответ два сотрудника, проверяем не включения в ответе сотрудника из другого отдела
//    // у сотрудников нет sales_team и salesPlan
//    // и сотрудника с другой ролью (driver)
//    /** @test */
//    public function empty_order()
//    {
//        // prev test
//        $this->loginUser();
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//        $this->session(['division' => $division->toArray()]);
//
//        // date
//        $now = CarbonImmutable::now();
//
//        /** @var $division_2 Division */
//        $division_2 = $this->divisionBuilder->create();
//
//        // roles
//        $adminRole = $this->roleBuilder
//            ->asAdmin()->create();
//        $managerRole = $this->roleBuilder
//            ->asManager()->create();
//        $driverRole = $this->roleBuilder
//            ->asDriver()->create();
//
//        // users/employees
//        $user_1 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($managerRole)->create();
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_1)
//            ->create();
//
//        $user_2 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($adminRole)->create();
//        $this->employeeBuilder
//            ->sales_team(SalesTeamEnum::Local)
//            ->user($user_2)
//            ->create();
//
//        // not check, unsuitable role
//        $user_3 = $this->userBuilder
//            ->division_ids($division)
//            ->roles($driverRole)
//            ->create();
//        $this->employeeBuilder
//            ->user($user_3)
//            ->sales_team(SalesTeamEnum::Local)
//            ->create();
//
//        // not check, unsuitable division
//        $user_4 = $this->userBuilder
//            ->division_ids($division_2)
//            ->roles($managerRole)
//            ->create();
//         $this->employeeBuilder
//             ->user($user_4)
//             ->sales_team(SalesTeamEnum::Local)
//             ->create();
//
//        // not check, not have sales_team
//        $user_5 = $this->userBuilder
//            ->division_ids($division_2)
//            ->roles($managerRole)
//            ->create();
//        $this->employeeBuilder
//            ->user($user_5)
//            ->sales_team(null)
//            ->create();
//
//         // filter
//        $filter = [
//            'filter' => [
//                'date' => $now->subMonth()->format('Y-m'),
//                'period-type' => 'by_creation',
//            ]
//        ];
//
//        // request
//        $res = $this->post(route('reports.sales.datatable'), $filter)
////            ->dump()
//            ->assertJson([
//                'draw' => 0,
//                'recordsTotal' => 26,
//                'recordsFiltered' => 26,
//                'cols' => [
//                    'col1',
//                    'col2'
//                ],
//                'input' => $filter
//            ])
//            ->assertJsonCount(26, 'data')
//            ->assertJsonCount(6, 'data.0')
//            ->assertJsonCount(6, 'data.1')
//            ->assertJsonCount(6, 'data.2')
//        ;
//
//        $data = $res->json('data');
//
//        //check result
//        $this->assertEquals($data[0]['id'], 1);
//        $this->assertEquals($data[0]['user_'.$user_1->id], "");
//        $this->assertEquals($data[0]['user_'.$user_2->id], "");
//        $this->assertEquals($data[0]['user_0'], "");
//        $this->assertEquals($data[0]['type'], ReportColumnEnum::LeadsTotal());
//        $this->assertEquals($data[0]['title'], ReportColumnEnum::LeadsTotal->label());
//
//        $this->assertEquals($data[1]['id'], 2);
//        $this->assertEquals($data[1]['user_'.$user_1->id], "");
//        $this->assertEquals($data[1]['user_'.$user_2->id], "");
//        $this->assertEquals($data[1]['user_0'], "");
//        $this->assertEquals($data[1]['type'], ReportColumnEnum::LeadsLost());
//        $this->assertEquals($data[1]['title'], ReportColumnEnum::LeadsLost->label());
//
//        $this->assertEquals($data[2]['id'], 3);
//        $this->assertEquals($data[2]['user_'.$user_1->id], "");
//        $this->assertEquals($data[2]['user_'.$user_2->id], "");
//        $this->assertEquals($data[2]['user_0'], "");
//        $this->assertEquals($data[2]['type'], ReportColumnEnum::LeadsLostCR());
//        $this->assertEquals($data[2]['title'], ReportColumnEnum::LeadsLostCR->label());
//
//        $this->assertEquals($data[3]['id'], 4);
//        $this->assertEquals($data[3]['user_'.$user_1->id], "");
//        $this->assertEquals($data[3]['user_'.$user_2->id], "");
//        $this->assertEquals($data[3]['user_0'], "");
//        $this->assertEquals($data[3]['type'], ReportColumnEnum::LeadsCalculated());
//        $this->assertEquals($data[3]['title'], ReportColumnEnum::LeadsCalculated->label());
//
//        $this->assertEquals($data[4]['id'], 5);
//        $this->assertEquals($data[4]['user_'.$user_1->id], "");
//        $this->assertEquals($data[4]['user_'.$user_2->id], "");
//        $this->assertEquals($data[4]['user_0'], "");
//        $this->assertEquals($data[4]['type'], ReportColumnEnum::LeadsCalculatedCR());
//        $this->assertEquals($data[4]['title'], ReportColumnEnum::LeadsCalculatedCR->label());
//
//        $this->assertEquals($data[5]['id'], 6);
//        $this->assertEquals($data[5]['user_'.$user_1->id], "");
//        $this->assertEquals($data[5]['user_'.$user_2->id], "");
//        $this->assertEquals($data[5]['user_0'], "");
//        $this->assertEquals($data[5]['type'], ReportColumnEnum::LeadsCalculatedSum());
//        $this->assertEquals($data[5]['title'], ReportColumnEnum::LeadsCalculatedSum->label());
//
//        $this->assertEquals($data[6]['id'], 7);
//        $this->assertEquals($data[6]['user_'.$user_1->id], "");
//        $this->assertEquals($data[6]['user_'.$user_2->id], "");
//        $this->assertEquals($data[6]['user_0'], "");
//        $this->assertEquals($data[6]['type'], ReportColumnEnum::LeadsBooked());
//        $this->assertEquals($data[6]['title'], ReportColumnEnum::LeadsBooked->label());
//
//        $this->assertEquals($data[7]['id'], 8);
//        $this->assertEquals($data[7]['user_'.$user_1->id], "");
//        $this->assertEquals($data[7]['user_'.$user_2->id], "");
//        $this->assertEquals($data[7]['user_0'], "");
//        $this->assertEquals($data[7]['type'], ReportColumnEnum::LeadsBookedCR());
//        $this->assertEquals($data[7]['title'], ReportColumnEnum::LeadsBookedCR->label());
//
//        $this->assertEquals($data[8]['id'], 9);
//        $this->assertEquals($data[8]['user_'.$user_1->id], "");
//        $this->assertEquals($data[8]['user_'.$user_2->id], "");
//        $this->assertEquals($data[8]['user_0'], "");
//        $this->assertEquals($data[8]['type'], ReportColumnEnum::LeadsBookedSum());
//        $this->assertEquals($data[8]['title'], ReportColumnEnum::LeadsBookedSum->label());
//
//        $this->assertEquals($data[9]['id'], 10);
//        $this->assertEquals($data[9]['user_'.$user_1->id], "");
//        $this->assertEquals($data[9]['user_'.$user_2->id], "");
//        $this->assertEquals($data[9]['user_0'], "");
//        $this->assertEquals($data[9]['type'], ReportColumnEnum::LeadsBookedFromCalculated());
//        $this->assertEquals($data[9]['title'], ReportColumnEnum::LeadsBookedFromCalculated->label());
//
//        $this->assertEquals($data[10]['id'], 11);
//        $this->assertEquals($data[10]['user_'.$user_1->id], "");
//        $this->assertEquals($data[10]['user_'.$user_2->id], "");
//        $this->assertEquals($data[10]['user_0'], "");
//        $this->assertEquals($data[10]['type'], ReportColumnEnum::LeadsBookedFromCalculatedCR());
//        $this->assertEquals($data[10]['title'], ReportColumnEnum::LeadsBookedFromCalculatedCR->label());
//
//        $this->assertEquals($data[11]['id'], 12);
//        $this->assertEquals($data[11]['user_'.$user_1->id], "");
//        $this->assertEquals($data[11]['user_'.$user_2->id], "");
//        $this->assertEquals($data[11]['user_0'], "");
//        $this->assertEquals($data[11]['type'], ReportColumnEnum::LeadsBookedFromCalculatedSum());
//        $this->assertEquals($data[11]['title'], ReportColumnEnum::LeadsBookedFromCalculatedSum->label());
//
//        $this->assertEquals($data[12]['id'], 13);
//        $this->assertEquals($data[12]['user_'.$user_1->id], "");
//        $this->assertEquals($data[12]['user_'.$user_2->id], "");
//        $this->assertEquals($data[12]['user_0'], "");
//        $this->assertEquals($data[12]['type'], ReportColumnEnum::LeadsSuccessful());
//        $this->assertEquals($data[12]['title'], ReportColumnEnum::LeadsSuccessful->label());
//
//        $this->assertEquals($data[13]['id'], 14);
//        $this->assertEquals($data[13]['user_'.$user_1->id], "");
//        $this->assertEquals($data[13]['user_'.$user_2->id], "");
//        $this->assertEquals($data[13]['user_0'], "");
//        $this->assertEquals($data[13]['type'], ReportColumnEnum::LeadsSuccessfulCR());
//        $this->assertEquals($data[13]['title'], ReportColumnEnum::LeadsSuccessfulCR->label());
//
//        $this->assertEquals($data[14]['id'], 15);
//        $this->assertEquals($data[14]['user_'.$user_1->id], "");
//        $this->assertEquals($data[14]['user_'.$user_2->id], "");
//        $this->assertEquals($data[14]['user_0'], "");
//        $this->assertEquals($data[14]['type'], ReportColumnEnum::SuccessRevenue());
//        $this->assertEquals($data[14]['title'], ReportColumnEnum::SuccessRevenue->label());
//
//        $this->assertEquals($data[15]['id'], 16);
//        $this->assertEquals($data[15]['user_'.$user_1->id], "");
//        $this->assertEquals($data[15]['user_'.$user_2->id], "");
//        $this->assertEquals($data[15]['user_0'], "");
//        $this->assertEquals($data[15]['type'], ReportColumnEnum::SuccessAOV());
//        $this->assertEquals($data[15]['title'], ReportColumnEnum::SuccessAOV->label());
//
//        $this->assertEquals($data[16]['id'], 17);
//        $this->assertEquals($data[16]['user_'.$user_1->id], "");
//        $this->assertEquals($data[16]['user_'.$user_2->id], "");
//        $this->assertEquals($data[16]['user_0'], "");
//        $this->assertEquals($data[16]['type'], ReportColumnEnum::LeadsDuplicate());
//        $this->assertEquals($data[16]['title'], ReportColumnEnum::LeadsDuplicate->label());
//
//        $this->assertEquals($data[17]['id'], 18);
//        $this->assertEquals($data[17]['user_'.$user_1->id], "");
//        $this->assertEquals($data[17]['user_'.$user_2->id], "");
//        $this->assertEquals($data[17]['user_0'], "");
//        $this->assertEquals($data[17]['type'], ReportColumnEnum::LeadsBadZip());
//        $this->assertEquals($data[17]['title'], ReportColumnEnum::LeadsBadZip->label());
//
//        $this->assertEquals($data[18]['id'], 19);
//        $this->assertEquals($data[18]['user_'.$user_1->id], "");
//        $this->assertEquals($data[18]['user_'.$user_2->id], "");
//        $this->assertEquals($data[18]['user_0'], "");
//        $this->assertEquals($data[18]['type'], ReportColumnEnum::LeadsCantService());
//        $this->assertEquals($data[18]['title'], ReportColumnEnum::LeadsCantService->label());
//
//        $this->assertEquals($data[19]['id'], 20);
//        $this->assertEquals($data[19]['user_'.$user_1->id], "");
//        $this->assertEquals($data[19]['user_'.$user_2->id], "");
//        $this->assertEquals($data[19]['user_0'], "");
//        $this->assertEquals($data[19]['type'], ReportColumnEnum::SalesPlan());
//        $this->assertEquals($data[19]['title'], ReportColumnEnum::SalesPlan->label());
//
//        $this->assertEquals($data[20]['id'], 21);
//        $this->assertEquals($data[20]['user_'.$user_1->id], "");
//        $this->assertEquals($data[20]['user_'.$user_2->id], "");
//        $this->assertEquals($data[20]['user_0'], "");
//        $this->assertEquals($data[20]['type'], ReportColumnEnum::Left());
//        $this->assertEquals($data[20]['title'], ReportColumnEnum::Left->label());
//
//        $this->assertEquals($data[21]['id'], 22);
//        $this->assertEquals($data[21]['user_'.$user_1->id], "");
//        $this->assertEquals($data[21]['user_'.$user_2->id], "");
//        $this->assertEquals($data[21]['user_0'], "");
//        $this->assertEquals($data[21]['type'], ReportColumnEnum::SalesFactCR());
//        $this->assertEquals($data[21]['title'], ReportColumnEnum::SalesFactCR->label());
//
//        $this->assertEquals($data[22]['id'], 23);
//        $this->assertEquals($data[22]['user_'.$user_1->id], "");
//        $this->assertEquals($data[22]['user_'.$user_2->id], "");
//        $this->assertEquals($data[22]['user_0'], "");
//        $this->assertEquals($data[22]['type'], ReportColumnEnum::SalesRank());
//        $this->assertEquals($data[22]['title'], ReportColumnEnum::SalesRank->label());
//
//        $this->assertEquals($data[23]['id'], 24);
//        $this->assertEquals($data[23]['user_'.$user_1->id], "");
//        $this->assertEquals($data[23]['user_'.$user_2->id], "");
//        $this->assertEquals($data[23]['user_0'], "");
//        $this->assertEquals($data[23]['type'], ReportColumnEnum::ConversionPlan());
//        $this->assertEquals($data[23]['title'], ReportColumnEnum::ConversionPlan->label());
//
//        $this->assertEquals($data[24]['id'], 25);
//        $this->assertEquals($data[24]['user_'.$user_1->id], "");
//        $this->assertEquals($data[24]['user_'.$user_2->id], "");
//        $this->assertEquals($data[24]['user_0'], "");
//        $this->assertEquals($data[24]['type'], ReportColumnEnum::ConversionFact());
//        $this->assertEquals($data[24]['title'], ReportColumnEnum::ConversionFact->label());
//
//        $this->assertEquals($data[25]['id'], 26);
//        $this->assertEquals($data[25]['user_'.$user_1->id], "");
//        $this->assertEquals($data[25]['user_'.$user_2->id], "");
//        $this->assertEquals($data[25]['user_0'], "");
//        $this->assertEquals($data[25]['type'], ReportColumnEnum::EfficiencyRank());
//        $this->assertEquals($data[25]['title'], ReportColumnEnum::EfficiencyRank->label());
//    }
//}
