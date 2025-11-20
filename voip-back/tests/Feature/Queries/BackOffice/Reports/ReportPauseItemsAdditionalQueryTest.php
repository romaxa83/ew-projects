<?php

namespace Tests\Feature\Queries\BackOffice\Reports;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Queries\BackOffice;
use App\Models\Employees\Employee;
use App\Models\Reports\PauseItem;
use App\Models\Reports\Report;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Reports\PauseItemBuilder;
use Tests\Builders\Reports\ReportBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class ReportPauseItemsAdditionalQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Reports\ReportPauseItemsAdditionalQuery::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected ReportBuilder $reportBuilder;
    protected PauseItemBuilder $pauseItemBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->pauseItemBuilder = resolve(PauseItemBuilder::class);
    }

    /** @test */
    public function success_get_data(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        $r_1 = $this->reportBuilder->create();
        /** @var $item_1_1 PauseItem */
        $item_1_1 = $this->pauseItemBuilder
            ->setReport($r_1)
            ->setPauseAt($date->subHours(50))
            ->setUnpauseAt($date->subHours(49))
            ->create();
        $item_1_2 = $this->pauseItemBuilder
            ->setReport($r_1)
            ->setPauseAt($date->subHours(40))
            ->setUnpauseAt($date->subHours(39))
            ->create();
        $item_1_3 = $this->pauseItemBuilder
            ->setReport($r_1)
            ->setPauseAt($date->subMinutes(40))
            ->setUnpauseAt($date->subMinutes(38))
            ->create();


        $r_2 = $this->reportBuilder->create();
        $item_2_1 = $this->pauseItemBuilder
            ->setReport($r_2)
            ->setPauseAt($date->subMinutes(40))
            ->setUnpauseAt($date->subMinutes(38))
            ->create();

        $m_3 = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'pause' => 3,
                        'total_pause_time' => (60*60) + (60*60) + (60*2),
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_get_data_employee(): void
    {
        $employee = $this->loginAsEmployee();

        $date = CarbonImmutable::now();

        $r_1 = $this->reportBuilder->setEmployee($employee)->create();
        /** @var $item_1_1 PauseItem */
        $item_1_1 = $this->pauseItemBuilder
            ->setReport($r_1)
            ->setPauseAt($date->subHours(50))
            ->setUnpauseAt($date->subHours(49))
            ->create();
        $item_1_2 = $this->pauseItemBuilder
            ->setReport($r_1)
            ->setPauseAt($date->subHours(40))
            ->setUnpauseAt($date->subHours(39))
            ->create();
        $item_1_3 = $this->pauseItemBuilder
            ->setReport($r_1)
            ->setPauseAt($date->subMinutes(40))
            ->setUnpauseAt($date->subMinutes(38))
            ->create();

        $r_2 = $this->reportBuilder->create();
        $item_1_3 = $this->pauseItemBuilder
            ->setReport($r_2)
            ->setPauseAt($date->subMinutes(40))
            ->setUnpauseAt($date->subMinutes(38))
            ->create();

        $m_3 = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($r_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'pause' => 3,
                        'total_pause_time' => (60*60) + (60*60) + (60*2),
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
                        'pause' => 0,
                        'total_pause_time' => 0,
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
                    pause
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
        $r_1 = $this->reportBuilder->setEmployee($employee)->create();

        $date = CarbonImmutable::now();

        $r_1 = $this->reportBuilder->create();
        /** @var $item_1_1 PauseItem */
        $item_1_1 = $this->pauseItemBuilder
            ->setReport($r_1)
            ->setPauseAt($date->subHours(50))
            ->setUnpauseAt($date->subHours(49))
            ->create();
        $item_1_2 = $this->pauseItemBuilder
            ->setReport($r_1)
            ->setPauseAt($date->subHours(2))
            ->setUnpauseAt($date->subHours(1))
            ->create();
        $item_1_3 = $this->pauseItemBuilder
            ->setReport($r_1)
            ->setPauseAt($date->subHours(60))
            ->setUnpauseAt($date->subHours(58))
            ->create();

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
                        'pause' => 2,
                        'total_pause_time' => (60*60) + ((60*60)*2),
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
                    pause
                    total_pause_time
                }
            }',
            self::QUERY,
            $id,
            $from,
            $to,
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

