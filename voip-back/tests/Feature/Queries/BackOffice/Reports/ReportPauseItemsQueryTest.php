<?php

namespace Tests\Feature\Queries\BackOffice\Reports;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Queries\BackOffice;
use App\Models\Employees\Employee;
use App\Models\Reports\PauseItem;
use App\Models\Reports\Report;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Reports\PauseItemBuilder;
use Tests\Builders\Reports\ReportBuilder;
use Tests\TestCase;

class ReportPauseItemsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Reports\ReportPauseItemsQuery::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected ReportBuilder $reportBuilder;
    protected PauseItemBuilder $pauseItemBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->pauseItemBuilder = resolve(PauseItemBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $report Report */
        $report = $this->reportBuilder->create();

        $m_1 = $this->pauseItemBuilder->setReport($report)->create();
        $m_2 = $this->pauseItemBuilder->setReport($report)->create();
        $m_3 = $this->pauseItemBuilder->setReport($report)->create();
        $this->pauseItemBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($report->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_3->id],
                            ['id' => $m_2->id],
                            ['id' => $m_1->id],
                        ],
                        'meta' => [
                            'total' => 3
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_paginator_as_employee(): void
    {
        $employee = $this->loginAsEmployee();

        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $m_1 = $this->pauseItemBuilder->setReport($report)->create();
        $m_2 = $this->pauseItemBuilder->setReport($report)->create();
        $m_3 = $this->pauseItemBuilder->setReport($report)->create();
        $this->pauseItemBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($report->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_3->id],
                            ['id' => $m_2->id],
                            ['id' => $m_1->id],
                        ],
                        'meta' => [
                            'total' => 3
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_with_page(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $report Report */
        $report = $this->reportBuilder->create();

        $m_1 = $this->pauseItemBuilder->setReport($report)->create();
        $m_2 = $this->pauseItemBuilder->setReport($report)->create();
        $m_3 = $this->pauseItemBuilder->setReport($report)->create();
        $this->pauseItemBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithPage($report->id,2)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'meta' => [
                            'total' => 3,
                            'per_page' => 10,
                            'current_page' => 2,
                            'from' => null,
                            'to' => null,
                            'last_page' => 1,
                            'has_more_pages' => false,
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrWithPage($reportID, $page): string
    {
        return sprintf(
            '
            {
                %s (
                    report_id: %s,
                    page: %s
                ) {
                    data {
                        id
                    }
                    meta {
                        total
                        per_page
                        current_page
                        from
                        to
                        last_page
                        has_more_pages
                    }
                }
            }',
            self::QUERY,
            $reportID,
            $page
        );
    }

    /** @test */
    public function success_with_per_page(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $report Report */
        $report = $this->reportBuilder->create();

        $m_1 = $this->pauseItemBuilder->setReport($report)->create();
        $m_2 = $this->pauseItemBuilder->setReport($report)->create();
        $m_3 = $this->pauseItemBuilder->setReport($report)->create();
        $this->pauseItemBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithPerPage($report->id,2)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'meta' => [
                            'total' => 3,
                            'per_page' => 2,
                            'current_page' => 1,
                            'from' => 1,
                            'to' => 2,
                            'last_page' => 2,
                            'has_more_pages' => true,
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrWithPerPage($reportID, $perPage): string
    {
        return sprintf(
            '
            {
                %s (report_id: %s, per_page: %s) {
                    data {
                        id
                    }
                    meta {
                        total
                        per_page
                        current_page
                        from
                        to
                        last_page
                        has_more_pages
                    }
                }
            }',
            self::QUERY,
            $reportID,
            $perPage
        );
    }

    /** @test */
    public function success_empty(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $report Report */
        $report = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($report->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'meta' => [
                            'total' => 0
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStr($reportID): string
    {
        return sprintf(
            '
            {
                %s (report_id: %s) {
                    data {
                        id
                    }
                    meta {
                        total
                        per_page
                        current_page
                        from
                        to
                        last_page
                        has_more_pages
                    }
                }
            }',
            self::QUERY,
            $reportID
        );
    }

    /** @test */
    public function filter_by_report_id(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        /** @var $pause_item_1 PauseItem */
        $pause_item_1 = $this->pauseItemBuilder->setReport($report)->create();

        $this->reportBuilder->create();
        $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($report->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $pause_item_1->id,
                                'pause_at' => $pause_item_1->pause_at,
                                'unpause_at' => $pause_item_1->unpause_at,
                                'duration' => $pause_item_1->getDiffAtBySec(),
                            ]
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrById($id): string
    {
        return sprintf(
            '
            {
                %s (report_id: %s){
                    data {
                        id
                        pause_at
                        unpause_at
                        duration
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }

    /** @test */
    public function filter_by_from_date(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date = CarbonImmutable::now();

        /** @var $pause_item_1 PauseItem */
        $pause_item_1 = $this->pauseItemBuilder
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->setReport($report)
            ->create();
        $pause_item_2 = $this->pauseItemBuilder
            ->setPauseAt($date->subHours(100))
            ->setUnpauseAt($date->subHours(98))
            ->setReport($report)
            ->create();

        $this->reportBuilder->create();
        $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByFromDate(
                $report->id,
                $date->subDays(1)->format(DatetimeEnum::DEFAULT_FORMAT)
            )
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $pause_item_1->id,]
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByFromDate($id, $from): string
    {
        return sprintf(
            '
            {
                %s (report_id: %s, date_from: "%s"){
                    data {
                        id
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $id,
            $from
        );
    }

    /** @test */
    public function filter_by_to_date(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date = CarbonImmutable::now();

        /** @var $pause_item_1 PauseItem */
        $pause_item_1 = $this->pauseItemBuilder
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->setReport($report)
            ->create();
        $pause_item_2 = $this->pauseItemBuilder
            ->setPauseAt($date->subHours(100))
            ->setUnpauseAt($date->subHours(98))
            ->setReport($report)
            ->create();

        $this->reportBuilder->create();
        $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByToDate(
                $report->id,
                $date->format(DatetimeEnum::DEFAULT_FORMAT)
            )
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $pause_item_2->id,],
                            ['id' => $pause_item_1->id,],
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByToDate($id, $to): string
    {
        return sprintf(
            '
            {
                %s (report_id: %s, date_to: "%s"){
                    data {
                        id
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $id,
            $to
        );
    }

    /** @test */
    public function filter_by_from_and_to_date(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date = CarbonImmutable::now();

        /** @var $pause_item_1 PauseItem */
        $pause_item_1 = $this->pauseItemBuilder
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->setReport($report)
            ->create();
        $pause_item_2 = $this->pauseItemBuilder
            ->setPauseAt($date->subHours(100))
            ->setUnpauseAt($date->subHours(98))
            ->setReport($report)
            ->create();
        $pause_item_3 = $this->pauseItemBuilder
            ->setPauseAt($date->addHours(10))
            ->setUnpauseAt($date->addHours(9))
            ->setReport($report)
            ->create();

        $this->reportBuilder->create();
        $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByFromAndToDate(
                $report->id,
                $date->format(DatetimeEnum::DEFAULT_FORMAT),
                $date->addDays(2)->format(DatetimeEnum::DEFAULT_FORMAT)
            )
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $pause_item_3->id,],
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByFromAndToDate($id, $from, $to): string
    {
        return sprintf(
            '
            {
                %s (report_id: %s, date_from: "%s", date_to: "%s"){
                    data {
                        id
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $id,
            $from,
            $to
        );
    }

    /** @test */
    public function not_perm_employee_another_report(): void
    {
        $employee = $this->loginAsEmployee();

        /** @var $report Report */
        $report = $this->reportBuilder->create();
        $report_2 = $this->reportBuilder->setEmployee($employee)->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($report->id)
        ])
        ;

        $this->assertPermission($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $report Report */
        $report = $this->reportBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($report->id)
        ])
        ;

        $this->assertPermission($res);
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $report Report */
        $report = $this->reportBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($report->id)
        ])
        ;

        $this->assertUnauthorized($res);
    }
}
