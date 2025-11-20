<?php

namespace Tests\Feature\Queries\BackOffice\Reports;

use App\Enums\Formats\DatetimeEnum;
use App\Enums\Reports\ReportStatus;
use App\GraphQL\Queries\BackOffice;
use App\Models\Employees\Employee;
use App\Models\Reports\Item;
use App\Models\Reports\Report;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Reports\ItemBuilder;
use Tests\Builders\Reports\PauseItemBuilder;
use Tests\Builders\Reports\ReportBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class ReportsAdditionalQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Reports\ReportsAdditionalQuery::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected ReportBuilder $reportBuilder;
    protected ItemBuilder $itemBuilder;
    protected PauseItemBuilder $pauseItemBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->pauseItemBuilder = resolve(PauseItemBuilder::class);
    }

    /** @test */
    public function success_get_data(): void
    {
        $this->loginAsSuperAdmin();

        $d_3 = $this->departmentBuilder->setData(['active' => false])->create();
        $e_3 = $this->employeeBuilder->setDepartment($d_3)->create();

        $r_1 = $this->reportBuilder->create();
        /** @var $item_1_1 Item */
        $item_1_1 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 40,
                'total_time' => 140,
            ])->create();
        $item_1_2 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 20,
                'total_time' => 14,
            ])->create();
        $item_1_3 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'status' => ReportStatus::NO_ANSWER,
                'wait' => 25,
                'total_time' => 154,
            ])->create();

        $date = CarbonImmutable::now();
        $pause_item_1_1 = $this->pauseItemBuilder->setReport($r_1)
            ->setPauseAt($date->subMinutes(40))
            ->setUnpauseAt($date->subMinutes(35))
            ->create();
        $pause_item_1_2 = $this->pauseItemBuilder->setReport($r_1)
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->create();

        $r_2 = $this->reportBuilder->create();
        $item_2_1 = $this->itemBuilder->setReport($r_2)
            ->setData([
                'status' => ReportStatus::TRANSFER,
                'wait' => 140,
                'total_time' => 10,
            ])->create();
        $item_2_2 = $this->itemBuilder->setReport($r_2)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 20,
                'total_time' => 148,
            ])->create();
        $pause_item_2_1 = $this->pauseItemBuilder->setReport($r_2)
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->create();

        $m_3 = $this->reportBuilder->create();

        $r_3 = $this->reportBuilder->setEmployee($e_3)->create();
        /** @var $item_1_1 Item */
        $item_3_1 = $this->itemBuilder->setReport($r_3)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 40,
                'total_time' => 140,
            ])->create();
        $item_3_2 = $this->itemBuilder->setReport($r_3)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 20,
                'total_time' => 14,
            ])->create();
        $item_3_3 = $this->itemBuilder->setReport($r_3)
            ->setData([
                'status' => ReportStatus::NO_ANSWER,
                'wait' => 25,
                'total_time' => 154,
            ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 5,
                        'total_answer_calls' => 3,
                        'total_dropped_calls' => 1,
                        'total_transfer_calls' => 1,
                        'total_wait' => $item_1_1->wait + $item_1_2->wait + $item_1_3->wait + $item_2_1->wait + $item_2_2->wait,
                        'total_time' => $item_1_1->total_time + $item_1_2->total_time + $item_1_3->total_time + $item_2_1->total_time + $item_2_2->total_time,
                        'total_pause' => 3,
                        'total_pause_time' => (5*60) + (2*60) + (2*60),
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_get_data_as_employee(): void
    {
        $employee = $this->loginAsEmployee();

        $r_1 = $this->reportBuilder->setEmployee($employee)->create();
        /** @var $item_1_1 Item */
        $item_1_1 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 40,
                'total_time' => 140,
            ])->create();
        $item_1_2 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 20,
                'total_time' => 14,
            ])->create();
        $item_1_3 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'status' => ReportStatus::NO_ANSWER,
                'wait' => 25,
                'total_time' => 154,
            ])->create();

        $date = CarbonImmutable::now();
        $pause_item_1_1 = $this->pauseItemBuilder->setReport($r_1)
            ->setPauseAt($date->subMinutes(40))
            ->setUnpauseAt($date->subMinutes(35))
            ->create();
        $pause_item_1_2 = $this->pauseItemBuilder->setReport($r_1)
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->create();

        $r_2 = $this->reportBuilder->create();
        $item_2_1 = $this->itemBuilder->setReport($r_2)
            ->setData([
                'status' => ReportStatus::TRANSFER,
                'wait' => 140,
                'total_time' => 10,
            ])->create();
        $item_2_2 = $this->itemBuilder->setReport($r_2)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 20,
                'total_time' => 148,
            ])->create();
        $pause_item_2_1 = $this->pauseItemBuilder->setReport($r_2)
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->create();

        $m_3 = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 3,
                        'total_answer_calls' => 2,
                        'total_dropped_calls' => 1,
                        'total_transfer_calls' => 0,
                        'total_wait' => $item_1_1->wait + $item_1_2->wait + $item_1_3->wait,
                        'total_time' => $item_1_1->total_time + $item_1_2->total_time + $item_1_3->total_time,
                        'total_pause' => 2,
                        'total_pause_time' => (5*60) + (2*60),
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty_data(): void
    {
        $this->loginAsSuperAdmin();

        $r_1 = $this->reportBuilder->create();
        $r_2 = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 0,
                        'total_answer_calls' => 0,
                        'total_dropped_calls' => 0,
                        'total_transfer_calls' => 0,
                        'total_wait' => 0,
                        'total_time' => 0,
                        'total_pause' => 0,
                        'total_pause_time' => 0,
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    total_calls
                    total_answer_calls
                    total_dropped_calls
                    total_transfer_calls
                    total_wait
                    total_time
                    total_pause
                    total_pause_time
                }
            }',
            self::QUERY
        );
    }

    /** @test */
    public function filter_by_department_id(): void
    {
        $this->loginAsSuperAdmin();

        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setDepartment($department_2)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $item_1_1 = $this->itemBuilder->setReport($report)
            ->setData([
                'wait' => 20,
                'total_time' => 120,
            ])->create();
        $item_1_2 = $this->itemBuilder->setReport($report)
            ->setData([
                'wait' => 40,
                'total_time' => 140,
            ])->create();
        $date = CarbonImmutable::now();
        $pause_item_1_1 = $this->pauseItemBuilder->setReport($report)
            ->setPauseAt($date->subMinutes(40))
            ->setUnpauseAt($date->subMinutes(35))
            ->create();

        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();
        $item_2_1 = $this->itemBuilder->setReport($report_2)
            ->setData([
                'wait' => 30,
                'total_time' => 10,
            ])->create();
        $pause_item_1_1 = $this->pauseItemBuilder->setReport($report_2)
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByDepartmentId($department->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 2,
                        'total_answer_calls' => 2,
                        'total_dropped_calls' => 0,
                        'total_transfer_calls' => 0,
                        'total_wait' => $item_1_1->wait + $item_1_2->wait,
                        'total_time' => $item_1_1->total_time + $item_1_2->total_time,
                        'total_pause' => 1,
                        'total_pause_time' => 5*60,
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByDepartmentId($id): string
    {
        return sprintf(
            '
            {
                %s (department_id: %s){
                    total_calls
                    total_answer_calls
                    total_dropped_calls
                    total_transfer_calls
                    total_wait
                    total_time
                    total_pause
                    total_pause_time
                }
            }',
            self::QUERY,
            $id
        );
    }

    /** @test */
    public function filter_by_date(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date = CarbonImmutable::now();

        /** @var $item_1 Item */
        $item_1 = $this->itemBuilder->setReport($report)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 40,
                'total_time' => 140,
                'call_at' => $date->subDays(3)
            ])->create();
        $item_2 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::ANSWERED,
            'wait' => 30,
            'total_time' => 130,
            'call_at' => $date->subDays(1)
        ])->create();
        $item_3 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::NO_ANSWER,
            'wait' => 30,
            'total_time' => 0,
            'call_at' => $date->subDays(2)
        ])->create();
        $item_4 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::TRANSFER,
            'wait' => 20,
            'total_time' => 10,
            'call_at' => $date->subDays(2)
        ])->create();
        $item_5 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::TRANSFER,
            'wait' => 230,
            'total_time' => 10,
            'call_at' => $date->subDays(3)
        ])->create();
        $item_6 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::TRANSFER,
            'wait' => 20,
            'total_time' => 30,
            'call_at' => $date->subDays(1)
        ])->create();

        $pause_item_1_1 = $this->pauseItemBuilder->setReport($report)
            ->setPauseAt($date->subHours(100))
            ->setUnpauseAt($date->subHours(99))
            ->create();
        $pause_item_1_2 = $this->pauseItemBuilder->setReport($report)
            ->setPauseAt($date->subHours(2))
            ->setUnpauseAt($date->subHours(1))
            ->create();
        $pause_item_1_3 = $this->pauseItemBuilder->setReport($report)
            ->setPauseAt($date->subHours(60))
            ->setUnpauseAt($date->subHours(58))
            ->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByDate(
                $date->subDays(3)->format(DatetimeEnum::DEFAULT_FORMAT),
                $date->subDays(2)->format(DatetimeEnum::DEFAULT_FORMAT)
            )
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 4,
                        'total_wait' => $item_1->wait + $item_3->wait + $item_4->wait + $item_5->wait,
                        'total_time' => $item_1->total_time + $item_3->total_time + $item_4->total_time + $item_5->total_time,
                        'total_pause' => 1,
                        'total_pause_time' => (60*60)*2,
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByDate($from, $to): string
    {
        return sprintf(
            '
            {
                %s (date_from: "%s", date_to: "%s"){
                    total_calls
                    total_wait
                    total_time
                    total_pause
                    total_pause_time
                }
            }',
            self::QUERY,
            $from,
            $to,
        );
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
        ;

        $this->assertPermission($res);
    }

    /** @test */
    public function not_auth(): void
    {
        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
        ;

        $this->assertUnauthorized($res);
    }
}
