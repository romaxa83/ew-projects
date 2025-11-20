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
use Tests\Builders\Reports\ReportBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class ReportItemsAdditionalQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Reports\ReportItemsAdditionalQuery::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected ReportBuilder $reportBuilder;
    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
    }

    /** @test */
    public function success_get_data(): void
    {
        $this->loginAsSuperAdmin();

        $r_1 = $this->reportBuilder->create();
        /** @var $item_1_1 Item */
        $item_1_1 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'wait' => 40,
                'total_time' => 140,
            ])->create();
        $item_1_2 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'wait' => 20,
                'total_time' => 14,
            ])->create();
        $item_1_3 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'wait' => 25,
                'total_time' => 154,
            ])->create();
        $item_1_4 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'status' => ReportStatus::NO_ANSWER,
                'wait' => 5,
                'total_time' => 15,
            ])->create();

        $r_2 = $this->reportBuilder->create();
        $item_2_1 = $this->itemBuilder->setReport($r_2)
            ->setData([
                'wait' => 140,
                'total_time' => 10,
            ])->create();

        $m_3 = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 4,
                        'total_dropped' => 1,
                        'total_wait' => $item_1_1->wait + $item_1_2->wait + $item_1_3->wait + $item_1_4->wait,
                        'total_time' => $item_1_1->total_time + $item_1_2->total_time + $item_1_3->total_time + $item_1_4->total_time,
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_get_data_employee(): void
    {
        $employee = $this->loginAsEmployee();

        $r_1 = $this->reportBuilder->setEmployee($employee)->create();
        /** @var $item_1_1 Item */
        $item_1_1 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'wait' => 40,
                'total_time' => 140,
            ])->create();
        $item_1_2 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'wait' => 20,
                'total_time' => 14,
            ])->create();
        $item_1_3 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'wait' => 25,
                'total_time' => 154,
            ])->create();
        $item_1_4 = $this->itemBuilder->setReport($r_1)
            ->setData([
                'status' => ReportStatus::NO_ANSWER,
                'wait' => 5,
                'total_time' => 15,
            ])->create();

        $r_2 = $this->reportBuilder->create();
        $item_2_1 = $this->itemBuilder->setReport($r_2)
            ->setData([
                'wait' => 140,
                'total_time' => 10,
            ])->create();

        $m_3 = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 4,
                        'total_dropped' => 1,
                        'total_wait' => $item_1_1->wait + $item_1_2->wait + $item_1_3->wait + $item_1_4->wait,
                        'total_time' => $item_1_1->total_time + $item_1_2->total_time + $item_1_3->total_time + $item_1_4->total_time,
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
            'query' => $this->getQueryStr($r_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 0,
                        'total_dropped' => 0,
                        'total_wait' => 0,
                        'total_time' => 0,
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            {
                %s (report_id: %s) {
                    total_calls
                    total_dropped
                    total_wait
                    total_time
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
        $r_1 = $this->reportBuilder->setEmployee($employee)->create();

        $date = CarbonImmutable::now();

        $this->loginAsSuperAdmin();

        $r_1 = $this->reportBuilder->create();
        /** @var $item_1_1 Item */
        $item_1_1 = $this->itemBuilder->setReport($r_1)
            ->setCallAt($date->subDays(4))
            ->setData([
                'wait' => 40,
                'total_time' => 140,
            ])->create();
        $item_1_2 = $this->itemBuilder->setReport($r_1)
            ->setCallAt($date->subDays(3))
            ->setData([
                'wait' => 20,
                'total_time' => 14,
            ])->create();
        $item_1_3 = $this->itemBuilder->setReport($r_1)
            ->setCallAt($date->subDays(2))
            ->setData([
                'wait' => 25,
                'total_time' => 154,
            ])->create();
        $item_1_4 = $this->itemBuilder->setReport($r_1)
            ->setCallAt($date->subDays(1))
            ->setData([
                'status' => ReportStatus::NO_ANSWER,
                'wait' => 5,
                'total_time' => 15,
            ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByDate(
                $r_1->id,
                $date->subDays(3)->format(DatetimeEnum::DEFAULT_FORMAT),
                $date->subDays(2)->format(DatetimeEnum::DEFAULT_FORMAT)
            )
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 2,
                        'total_dropped' => 0,
                        'total_wait' => $item_1_2->wait + $item_1_3->wait,
                        'total_time' => $item_1_2->total_time + $item_1_3->total_time,
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByDate($id, $from, $to): string
    {
        return sprintf(
            '
            {
                %s (report_id:%s, date_from: "%s", date_to: "%s"){
                    total_calls
                    total_dropped
                    total_wait
                    total_time
                }
            }',
            self::QUERY,
            $id,
            $from,
            $to,
        );
    }

    /** @test */
    public function filter_by_status(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $report Report */
        $r_1 = $this->reportBuilder->setEmployee($employee)->create();

        $date = CarbonImmutable::now();

        $this->loginAsSuperAdmin();

        $r_1 = $this->reportBuilder->create();
        /** @var $item_1_1 Item */
        $item_1_1 = $this->itemBuilder->setReport($r_1)
            ->setCallAt($date->subDays(4))
            ->setData([
                'status' => ReportStatus::TRANSFER,
                'wait' => 40,
                'total_time' => 140,
            ])->create();
        $item_1_2 = $this->itemBuilder->setReport($r_1)
            ->setCallAt($date->subDays(3))
            ->setData([
                'wait' => 20,
                'total_time' => 14,
            ])->create();
        $item_1_3 = $this->itemBuilder->setReport($r_1)
            ->setCallAt($date->subDays(2))
            ->setData([
                'status' => ReportStatus::TRANSFER,
                'wait' => 25,
                'total_time' => 154,
            ])->create();
        $item_1_4 = $this->itemBuilder->setReport($r_1)
            ->setCallAt($date->subDays(1))
            ->setData([
                'status' => ReportStatus::NO_ANSWER,
                'wait' => 5,
                'total_time' => 15,
            ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatus(
                $r_1->id,
                ReportStatus::TRANSFER,
            )
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'total_calls' => 2,
                        'total_dropped' => 0,
                        'total_wait' => $item_1_1->wait + $item_1_3->wait,
                        'total_time' => $item_1_1->total_time + $item_1_3->total_time,
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByStatus($id, $status): string
    {
        return sprintf(
            '
            {
                %s (report_id:%s, status: %s){
                    total_calls
                    total_dropped
                    total_wait
                    total_time
                }
            }',
            self::QUERY,
            $id,
            $status,
        );
    }

    /** @test */
    public function not_perm_employee_another_report(): void
    {
        $employee = $this->loginAsEmployee();

        $r_1 = $this->reportBuilder->create();
        $r_2 = $this->reportBuilder->setEmployee($employee)->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r_1->id)
        ])
        ;

        $this->assertPermission($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        $r_1 = $this->reportBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r_1->id)
        ])
        ;

        $this->assertPermission($res);
    }

    /** @test */
    public function not_auth(): void
    {
        $r_1 = $this->reportBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r_1->id)
        ])
        ;

        $this->assertUnauthorized($res);
    }
}

