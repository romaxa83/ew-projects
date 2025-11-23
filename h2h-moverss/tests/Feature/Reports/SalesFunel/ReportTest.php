<?php

namespace Tests\Feature\Reports\SalesFunel;

use App\Enums\Employee\SalesTeamEnum;
use App\Enums\Reports\ReportColumnEnum;
use App\Models\Division;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Orders\EstimateBuilder;
use Tests\Builders\Orders\EstimateCalculatedBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\PaymentBuilder;
use Tests\Builders\Orders\StatusBuilder;
use Tests\Builders\Orders\StatusGroupBuilder;
use Tests\Builders\Orders\StatusHistoryBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected StatusGroupBuilder $statusGroupBuilder;
    protected StatusBuilder $statusBuilder;
    protected StatusHistoryBuilder $statusHistoryBuilder;
    protected OrderBuilder $orderBuilder;
    protected EstimateCalculatedBuilder $estimateCalculatedBuilder;
    protected EstimateBuilder $estimateBuilder;
    protected PaymentBuilder $paymentBuilder;
    protected EmployeeBuilder $employeeBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->statusGroupBuilder = resolve(StatusGroupBuilder::class);
        $this->statusBuilder = resolve(StatusBuilder::class);
        $this->statusHistoryBuilder = resolve(StatusHistoryBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->estimateCalculatedBuilder = resolve(EstimateCalculatedBuilder::class);
        $this->estimateBuilder = resolve(EstimateBuilder::class);
        $this->paymentBuilder = resolve(PaymentBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function get_lead_data()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $statusNewLead = $this->statusBuilder
            ->asNewLead()
            ->create();

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(1)
            ->create();
        $status_group_2 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(2)
            ->create();

        $status_1_1 = $this->statusBuilder
            ->asSuccess()
            ->group($status_group_1)
            ->create();
        $status_1_2 = $this->statusBuilder
            ->group($status_group_1)
            ->asDuplicate()
            ->create();

        $status_2_1 = $this->statusBuilder
            ->group($status_group_2)
            ->asDone()
            ->create();
        $status_2_2 = $this->statusBuilder
            ->group($status_group_2)
            ->asSalesDone()
            ->create();


        $order_1 = $this->orderBuilder
            ->division($division)
            ->status($status_1_2)
            ->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_3 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($status_1_1)
            ->new_status($status_1_2)
            ->created_at($now->subDays(2))
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_1)
            ->title('total')
            ->value("$100.50")
            ->create();

        $order_2 = $this->orderBuilder
            ->status($status_2_2)
            ->division($division)->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($status_2_1)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_3 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($status_2_1)
            ->new_status($status_2_2)
            ->created_at($now->subDays(2))
            ->create();
        $this->paymentBuilder
            ->order($order_2)->amount(50.0000)->create();
        $this->paymentBuilder
            ->order($order_2)->amount(150.0000)->create();

        $order_3 = $this->orderBuilder
            ->status($status_1_2)
            ->division($division)->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(1))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($status_1_2)
            ->created_at($now->subDays(1))
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_3)
            ->title('total')
            ->value("$55.50")
            ->create();

        $order_4 = $this->orderBuilder
            ->status($statusNewLead)
            ->division($division)->create();
        $order_history_4_1 = $this->statusHistoryBuilder
            ->order_id($order_4)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_5 = $this->orderBuilder
            ->division($division)->create();
        $order_history_5_1 = $this->statusHistoryBuilder
            ->order_id($order_5)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();

        $filter = [
            'date_start' => $now->subDays(4)->format('Y-m-d'),
            'date_end' => $now->format('Y-m-d'),
        ];

        $res = $this->post(route('reports.sales.funel.report.data'), $filter)
            ->assertJson([
                'success' => true,
                'data' => [
                    'headers' => [
                        'Metric',
                        $status_group_1->title,
                        $status_group_2->title,
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.headers')
        ;

        $data = $res->json('data.records');

        //check result
        $this->assertEquals(
            $data[0]['title'],
            ReportColumnEnum::LeadsQty->label()
        );
        $this->assertEquals(
            $data[0][$status_group_1->title],
            2
        );
        $this->assertEquals(
            $data[0][$status_group_2->title],
            1
        );

        $this->assertEquals(
            $data[1]['title'],
            ReportColumnEnum::LeadsSum->label()
        );
        $this->assertEquals(
            $data[1][$status_group_1->title],
            '$'. 55.50 + 100.50
        );
        $this->assertEquals(
            $data[1][$status_group_2->title],
            '$'. 50 + 150
        );

        $this->assertEquals(
            $data[2]['title'],
            ReportColumnEnum::LeadsCR->label()
        );
        $this->assertEquals(
            $data[2][$status_group_1->title],
            round((2/5) * 100,2) . '%'
        );
        $this->assertEquals(
            $data[2][$status_group_2->title],
            round((1/5) * 100,2) . '%'
        );
    }

    /** @test */
    public function get_lost_data()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $statusNewLead = $this->statusBuilder
            ->asNewLead()
            ->create();
        $statusLost = $this->statusBuilder
            ->asLost()
            ->create();

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(1)
            ->create();
        $status_group_2 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(2)
            ->create();

        $status_1_1 = $this->statusBuilder
            ->asSuccess()
            ->group($status_group_1)
            ->create();
        $status_1_2 = $this->statusBuilder
            ->group($status_group_1)
            ->asDuplicate()
            ->create();

        $status_2_1 = $this->statusBuilder
            ->group($status_group_2)
            ->asDone()
            ->create();
        $status_2_2 = $this->statusBuilder
            ->group($status_group_2)
            ->asSalesDone()
            ->create();

        $order_1 = $this->orderBuilder
            ->division($division)
            ->status($statusLost)
            ->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_3 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($status_1_1)
            ->new_status($statusLost)
            ->created_at($now->subDays(2))
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_1)
            ->title('total')
            ->value("$100.50")
            ->create();

        $order_2 = $this->orderBuilder
            ->status($statusLost)
            ->division($division)->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($status_1_2)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_3 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($status_1_1)
            ->new_status($statusLost)
            ->created_at($now->subDays(2))
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_2)
            ->title('total')
            ->value("$130.50")
            ->create();

        $order_3 = $this->orderBuilder
            ->status($statusLost)
            ->division($division)->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(1))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($status_2_1)
            ->created_at($now->subDays(1))
            ->create();
        $order_history_1_3 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($status_2_1)
            ->new_status($statusLost)
            ->created_at($now->subDays(1))
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_3)
            ->title('total')
            ->value("$55.50")
            ->create();

        $order_4 = $this->orderBuilder
            ->status($statusNewLead)
            ->division($division)->create();
        $order_history_4_1 = $this->statusHistoryBuilder
            ->order_id($order_4)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_5 = $this->orderBuilder
            ->division($division)->create();
        $order_history_5_1 = $this->statusHistoryBuilder
            ->order_id($order_5)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();

        $filter = [
            'date_start' => $now->subDays(4)->format('Y-m-d'),
            'date_end' => $now->format('Y-m-d'),
        ];

        $res = $this->post(route('reports.sales.funel.report.data'), $filter)
            ->assertJson([
                'success' => true,
                'data' => [
                    'headers' => [
                        'Metric',
                        $status_group_1->title,
                        $status_group_2->title,
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.headers')
        ;

        $data = $res->json('data.records');

        //check result
        $this->assertEquals(
            $data[3]['title'],
            ReportColumnEnum::LeadsLost->label()
        );
        $this->assertEquals(
            $data[3][$status_group_1->title],
            2
        );
        $this->assertEquals(
            $data[3][$status_group_2->title],
            1
        );

        $this->assertEquals(
            $data[4]['title'],
            ReportColumnEnum::LeadsLostSum->label()
        );
        $this->assertEquals(
            $data[4][$status_group_1->title],
            '$'. 130.50 + 100.50
        );
        $this->assertEquals(
            $data[4][$status_group_2->title],
            '$'. 55.5
        );

        $this->assertEquals(
            $data[5]['title'],
            ReportColumnEnum::LeadsLostCR->label()
        );
        $this->assertEquals(
            $data[5][$status_group_1->title],
            round((2/5) * 100,2) . '%'
        );
        $this->assertEquals(
            $data[5][$status_group_2->title],
            round((1/5) * 100,2) . '%'
        );
    }

    /** @test */
    public function get_lead_data_filter_by_date()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $statusNewLead = $this->statusBuilder
            ->asNewLead()
            ->create();

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(1)
            ->create();
        $status_group_2 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(2)
            ->create();

        $status_1_1 = $this->statusBuilder
            ->asSuccess()
            ->group($status_group_1)
            ->create();
        $status_1_2 = $this->statusBuilder
            ->group($status_group_1)
            ->asDuplicate()
            ->create();

        $status_2_1 = $this->statusBuilder
            ->group($status_group_2)
            ->asDone()
            ->create();
        $status_2_2 = $this->statusBuilder
            ->group($status_group_2)
            ->asSalesDone()
            ->create();


        $order_1 = $this->orderBuilder
            ->division($division)
            ->status($status_1_2)
            ->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(1))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(1))
            ->create();
        $order_history_1_3 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($status_1_1)
            ->new_status($status_1_2)
            ->created_at($now->subDays(1))
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_1)
            ->title('total')
            ->value("$100.50")
            ->create();

        $order_2 = $this->orderBuilder
            ->status($status_2_2)
            ->division($division)->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(5))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($status_2_1)
            ->created_at($now->subDays(5))
            ->create();
        $order_history_1_3 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($status_2_1)
            ->new_status($status_2_2)
            ->created_at($now->subDays(5))
            ->create();
        $this->paymentBuilder
            ->order($order_2)->amount(50.0000)->create();
        $this->paymentBuilder
            ->order($order_2)->amount(150.0000)->create();

        $order_3 = $this->orderBuilder
            ->status($status_1_2)
            ->division($division)->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(6))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($status_1_2)
            ->created_at($now->subDays(6))
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_3)
            ->title('total')
            ->value("$55.50")
            ->create();

        $order_4 = $this->orderBuilder
            ->status($statusNewLead)
            ->division($division)->create();
        $order_history_4_1 = $this->statusHistoryBuilder
            ->order_id($order_4)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(6))
            ->create();
        $order_5 = $this->orderBuilder
            ->division($division)->create();
        $order_history_5_1 = $this->statusHistoryBuilder
            ->order_id($order_5)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(6))
            ->create();

        $filter = [
            'date_start' => $now->subDays(2)->format('Y-m-d'),
            'date_end' => $now->format('Y-m-d'),
        ];

        $res = $this->post(route('reports.sales.funel.report.data'), $filter)
//            ->dump()
            ->assertJson([
                'success' => true,
                'data' => [
                    'headers' => [
                        'Metric',
                        $status_group_1->title,
                        $status_group_2->title,
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.headers')
        ;

        $data = $res->json('data.records');

        //check result
        $this->assertEquals(
            $data[0]['title'],
            ReportColumnEnum::LeadsQty->label()
        );
        $this->assertEquals(
            $data[0][$status_group_1->title],
            1
        );
        $this->assertEquals(
            $data[0][$status_group_2->title],
            0
        );

        $this->assertEquals(
            $data[1]['title'],
            ReportColumnEnum::LeadsSum->label()
        );
        $this->assertEquals(
            $data[1][$status_group_1->title],
            '$'. 100.50
        );
        $this->assertEquals(
            $data[1][$status_group_2->title],
            '$0'
        );

        $this->assertEquals(
            $data[2]['title'],
            ReportColumnEnum::LeadsCR->label()
        );
        $this->assertEquals(
            $data[2][$status_group_1->title],
            round((1/1) * 100,2) . '%'
        );
        $this->assertEquals(
            $data[2][$status_group_2->title],
            '0%'
        );

        $this->assertEquals(
            $data[3]['title'],
            ReportColumnEnum::LeadsLost->label()
        );
        $this->assertEquals(
            $data[3][$status_group_1->title],
            0
        );
        $this->assertEquals(
            $data[3][$status_group_2->title],
            0
        );
    }

    /** @test */
    public function get_lead_data_filter_by_user()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $user_1 = $this->userBuilder->create();
        $user_2 = $this->userBuilder->create();

        $statusNewLead = $this->statusBuilder
            ->asNewLead()
            ->create();

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(1)
            ->create();

        $status_1_1 = $this->statusBuilder
            ->asSuccess()
            ->group($status_group_1)
            ->create();
        $status_1_2 = $this->statusBuilder
            ->group($status_group_1)
            ->asDuplicate()
            ->create();

        $order_1 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($status_1_2)
            ->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $order_2 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($status_1_2)
            ->create();
        $order_history_2_1 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_2_2 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $order_3 = $this->orderBuilder
            ->division($division)
            ->manager($user_2)
            ->status($status_1_2)
            ->create();
        $order_history_3_1 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_3_2 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $filter = [
            'date_start' => $now->subDays(4)->format('Y-m-d'),
            'date_end' => $now->format('Y-m-d'),
            'user_id' => $user_1->id,
        ];

        $res = $this->post(route('reports.sales.funel.report.data'), $filter)
            ->assertJson([
                'success' => true,
                'data' => [
                    'headers' => [
                        'Metric',
                        $status_group_1->title,
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.headers')
        ;

        $data = $res->json('data.records');

        //check result
        $this->assertEquals(
            $data[0]['title'],
            ReportColumnEnum::LeadsQty->label()
        );
        $this->assertEquals(
            $data[0][$status_group_1->title],
            2
        );
    }

    /** @test */
    public function get_lead_data_filter_by_sales_team_as_local()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $user_1 = $this->userBuilder->create();
        $this->employeeBuilder
            ->user($user_1)
            ->sales_team(SalesTeamEnum::Local)
            ->create();
        $user_2 = $this->userBuilder->create();
        $this->employeeBuilder
            ->user($user_2)
            ->sales_team(SalesTeamEnum::Local)
            ->create();
        $user_3 = $this->userBuilder->create();
        $this->employeeBuilder
            ->user($user_3)
            ->sales_team(SalesTeamEnum::Local_long)
            ->create();

        $statusNewLead = $this->statusBuilder
            ->asNewLead()
            ->create();

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(1)
            ->create();

        $status_1_1 = $this->statusBuilder
            ->asSuccess()
            ->group($status_group_1)
            ->create();
        $status_1_2 = $this->statusBuilder
            ->group($status_group_1)
            ->asDuplicate()
            ->create();

        $order_1 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($status_1_2)
            ->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $order_2 = $this->orderBuilder
            ->division($division)
            ->manager($user_2)
            ->status($status_1_2)
            ->create();
        $order_history_2_1 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_2_2 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $order_3 = $this->orderBuilder
            ->division($division)
            ->manager($user_3)
            ->status($status_1_2)
            ->create();
        $order_history_3_1 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_3_2 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $filter = [
            'date_start' => $now->subDays(4)->format('Y-m-d'),
            'date_end' => $now->format('Y-m-d'),
            'sales_team' => SalesTeamEnum::Local(),
        ];

        $res = $this->post(route('reports.sales.funel.report.data'), $filter)
            ->assertJson([
                'success' => true,
                'data' => [
                    'headers' => [
                        'Metric',
                        $status_group_1->title,
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.headers')
        ;

        $data = $res->json('data.records');

        //check result
        $this->assertEquals(
            $data[0]['title'],
            ReportColumnEnum::LeadsQty->label()
        );
        $this->assertEquals(
            $data[0][$status_group_1->title],
            2
        );
    }

    /** @test */
    public function get_lead_data_filter_by_sales_team_as_na()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $user_1 = $this->userBuilder->create();
        $this->employeeBuilder
            ->user($user_1)
            ->sales_team(SalesTeamEnum::Local)
            ->create();
        $user_2 = $this->userBuilder->create();
        $this->employeeBuilder
            ->user($user_2)
            ->sales_team(null)
            ->create();
        $user_3 = $this->userBuilder->create();
        $this->employeeBuilder
            ->user($user_3)
            ->sales_team(SalesTeamEnum::Local_long)
            ->create();

        $statusNewLead = $this->statusBuilder
            ->asNewLead()
            ->create();

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(1)
            ->create();

        $status_1_1 = $this->statusBuilder
            ->asSuccess()
            ->group($status_group_1)
            ->create();
        $status_1_2 = $this->statusBuilder
            ->group($status_group_1)
            ->asDuplicate()
            ->create();

        $order_1 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($status_1_2)
            ->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $order_2 = $this->orderBuilder
            ->division($division)
            ->manager($user_2)
            ->status($status_1_2)
            ->create();
        $order_history_2_1 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_2_2 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $order_3 = $this->orderBuilder
            ->division($division)
            ->manager($user_3)
            ->status($status_1_2)
            ->create();
        $order_history_3_1 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_3_2 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $filter = [
            'date_start' => $now->subDays(4)->format('Y-m-d'),
            'date_end' => $now->format('Y-m-d'),
            'sales_team' => 'na',
        ];

        $res = $this->post(route('reports.sales.funel.report.data'), $filter)
            ->assertJson([
                'success' => true,
                'data' => [
                    'headers' => [
                        'Metric',
                        $status_group_1->title,
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.headers')
        ;

        $data = $res->json('data.records');

        //check result
        $this->assertEquals(
            $data[0]['title'],
            ReportColumnEnum::LeadsQty->label()
        );
        $this->assertEquals(
            $data[0][$status_group_1->title],
            1
        );
    }

    /** @test */
    public function get_lead_data_without_filter_by_date()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $user_1 = $this->userBuilder->create();
        $this->employeeBuilder
            ->user($user_1)
            ->sales_team(SalesTeamEnum::Local)
            ->create();
        $user_2 = $this->userBuilder->create();
        $this->employeeBuilder
            ->user($user_2)
            ->sales_team(null)
            ->create();
        $user_3 = $this->userBuilder->create();
        $this->employeeBuilder
            ->user($user_3)
            ->sales_team(SalesTeamEnum::Local_long)
            ->create();

        $statusNewLead = $this->statusBuilder
            ->asNewLead()
            ->create();

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(1)
            ->create();

        $status_1_1 = $this->statusBuilder
            ->asSuccess()
            ->group($status_group_1)
            ->create();
        $status_1_2 = $this->statusBuilder
            ->group($status_group_1)
            ->asDuplicate()
            ->create();

        $order_1 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($status_1_2)
            ->create();
        $order_history_1_1 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now->subDays(2))
            ->create();
        $order_history_1_2 = $this->statusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now->subDays(2))
            ->create();

        $order_2 = $this->orderBuilder
            ->division($division)
            ->manager($user_2)
            ->status($status_1_2)
            ->create();
        $order_history_2_1 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now)
            ->create();
        $order_history_2_2 = $this->statusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now)
            ->create();

        $order_3 = $this->orderBuilder
            ->division($division)
            ->manager($user_3)
            ->status($status_1_2)
            ->create();
        $order_history_3_1 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status(null)
            ->new_status($statusNewLead)
            ->created_at($now)
            ->create();
        $order_history_3_2 = $this->statusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($status_1_1)
            ->created_at($now)
            ->create();

        $filter = [];

        $res = $this->post(route('reports.sales.funel.report.data'), $filter)
            ->assertJson([
                'success' => true,
                'data' => [
                    'headers' => [
                        'Metric',
                        $status_group_1->title,
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.headers')
        ;

        $data = $res->json('data.records');

        //check result
        $this->assertEquals(
            $data[0]['title'],
            ReportColumnEnum::LeadsQty->label()
        );
        $this->assertEquals(
            $data[0][$status_group_1->title],
            2
        );
    }

    /** @test */
    public function get_empty_data()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $status_group_1 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(1)
            ->create();
        $status_group_2 = $this->statusGroupBuilder
            ->in_funel_report(1)
            ->sort(2)
            ->create();

        $status_1_1 = $this->statusBuilder
            ->asSuccess()
            ->group($status_group_1)
            ->create();
        $status_1_2 = $this->statusBuilder
            ->group($status_group_1)
            ->asDuplicate()
            ->create();

        $status_2_1 = $this->statusBuilder
            ->group($status_group_2)
            ->asDone()
            ->create();
        $status_2_2 = $this->statusBuilder
            ->group($status_group_2)
            ->asSalesDone()
            ->create();

        $filter = [
            'date_start' => $now->subDays(4)->format('Y-m-d'),
            'date_end' => $now->format('Y-m-d'),
        ];

        $res = $this->post(route('reports.sales.funel.report.data'), $filter)
            ->assertJson([
                'success' => true,
                'data' => [
                    'headers' => [
                        'Metric',
                        $status_group_1->title,
                        $status_group_2->title,
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.headers')
        ;

        $data = $res->json('data.records');

        //check result
        $this->assertEquals(
            $data[0]['title'],
            ReportColumnEnum::LeadsQty->label()
        );
        $this->assertEquals(
            $data[0][$status_group_1->title],
            0
        );
        $this->assertEquals(
            $data[0][$status_group_2->title],
            0
        );

        $this->assertEquals(
            $data[1]['title'],
            ReportColumnEnum::LeadsSum->label()
        );
        $this->assertEquals(
            $data[1][$status_group_1->title],
            '$0'
        );
        $this->assertEquals(
            $data[1][$status_group_2->title],
            '$0'
        );

        $this->assertEquals(
            $data[2]['title'],
            ReportColumnEnum::LeadsCR->label()
        );
        $this->assertEquals(
            $data[2][$status_group_1->title],
            '0%'
        );
        $this->assertEquals(
            $data[2][$status_group_2->title],
            '0%'
        );

        $this->assertEquals(
            $data[3]['title'],
            ReportColumnEnum::LeadsLost->label()
        );
        $this->assertEquals(
            $data[3][$status_group_1->title],
            0
        );
        $this->assertEquals(
            $data[3][$status_group_2->title],
            0
        );

        $this->assertEquals(
            $data[4]['title'],
            ReportColumnEnum::LeadsLostSum->label()
        );
        $this->assertEquals(
            $data[4][$status_group_1->title],
            '$0'
        );
        $this->assertEquals(
            $data[4][$status_group_2->title],
            '$0'
        );

        $this->assertEquals(
            $data[5]['title'],
            ReportColumnEnum::LeadsLostCR->label()
        );
        $this->assertEquals(
            $data[5][$status_group_1->title],
            '0%'
        );
        $this->assertEquals(
            $data[5][$status_group_2->title],
            '0%'
        );
    }
}
