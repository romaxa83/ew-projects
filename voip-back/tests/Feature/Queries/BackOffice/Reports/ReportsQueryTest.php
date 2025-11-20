<?php

namespace Tests\Feature\Queries\BackOffice\Reports;

use App\Enums\Formats\DatetimeEnum;
use App\Enums\Reports\ReportStatus;
use App\GraphQL\Queries\BackOffice;
use App\Models\Employees\Employee;
use App\Models\Reports\Item;
use App\Models\Reports\PauseItem;
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

class ReportsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Reports\ReportsQuery::NAME;

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
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        $m_1 = $this->reportBuilder->create();
        $m_2 = $this->reportBuilder->create();
        $m_3 = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
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
    public function success_paginator_only_active_department(): void
    {
        $this->loginAsSuperAdmin();

        $d_1 = $this->departmentBuilder->setData(['active' => true])->create();
        $d_2 = $this->departmentBuilder->setData(['active' => false])->create();
        $d_3 = $this->departmentBuilder->setData(['active' => true])->create();

        $e_1 = $this->employeeBuilder->setDepartment($d_1)->create();
        $e_2 = $this->employeeBuilder->setDepartment($d_2)->create();
        $e_3_1 = $this->employeeBuilder->setDepartment($d_3)->create();
        $e_3_2 = $this->employeeBuilder->setDepartment($d_3)->create();

        $r_1 = $this->reportBuilder->setEmployee($e_1)->create();
        $r_2 = $this->reportBuilder->setEmployee($e_2)->create();
        $r_3_1 = $this->reportBuilder->setEmployee($e_3_1)->create();
        $r_3_2 = $this->reportBuilder->setEmployee($e_3_2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $r_3_2->id],
                            ['id' => $r_3_1->id],
                            ['id' => $r_1->id],
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
    public function success_paginator_with_trashed(): void
    {
        $this->loginAsSuperAdmin();

        $employee = $this->employeeBuilder->isDeleted()->create();

        $m_1 = $this->reportBuilder->setEmployee($employee)->create();
        $m_2 = $this->reportBuilder->create();
        $m_3 = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
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

        $m_1 = $this->reportBuilder->setEmployee($employee)->create();
        $m_2 = $this->reportBuilder->create();
        $m_3 = $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_1->id],
                        ],
                        'meta' => [
                            'total' => 1
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

        $this->reportBuilder->create();
        $this->reportBuilder->create();
        $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithPage(2)
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

    protected function getQueryStrWithPage($page): string
    {
        return sprintf(
            '
            {
                %s (page: %s) {
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
            $page
        );
    }

    /** @test */
    public function success_with_per_page(): void
    {
        $this->loginAsSuperAdmin();

        $this->reportBuilder->create();
        $this->reportBuilder->create();
        $this->reportBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithPerPage(2)
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

    protected function getQueryStrWithPerPage($perPage): string
    {
        return sprintf(
            '
            {
                %s (per_page: %s) {
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
            $perPage
        );
    }

    /** @test */
    public function success_empty(): void
    {
        $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
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

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
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
            self::QUERY
        );
    }

    /** @test */
    public function filter_by_id(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        /** @var $item_1 Item */
        $item_1 = $this->itemBuilder->setReport($report)->create();
        $item_2 = $this->itemBuilder->setReport($report)->create();

        $date = CarbonImmutable::now();
        /** @var $pause_item_1 PauseItem */
        $pause_item_1 = $this->pauseItemBuilder
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->setReport($report)
            ->create();

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
                                'id' => $report->id,
                                'employee' => [
                                    'id' => $employee->id
                                ],
                                'items' => [
                                    [
                                        'id' => $item_1->id,
                                        'status' => $item_1->status,
                                        'number' => $item_1->num,
                                        'name' => $item_1->name,
                                        'wait' => $item_1->wait,
                                        'total_time' => $item_1->total_time,
                                        'call_at' => $item_1->call_at,
                                    ],
                                    ['id' => $item_2->id]
                                ],
                                'pause_items' => [
                                    [
                                        'id' => $pause_item_1->id,
                                        'pause_at' => $pause_item_1->pause_at,
                                        'unpause_at' => $pause_item_1->unpause_at,
                                        'duration' => 60*2,
                                    ]
                                ]
                            ]
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'. self::QUERY .'.data.0.items')
            ->assertJsonCount(1, 'data.'. self::QUERY .'.data.0.pause_items')
        ;
    }

    /** @test */
    public function filter_by_id_asset_counters(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        /** @var $item_1 Item */
        $item_1 = $this->itemBuilder->setReport($report)
            ->setData([
                'status' => ReportStatus::ANSWERED,
                'wait' => 40,
                'total_time' => 140,
            ])->create();
        $item_2 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::ANSWERED,
            'wait' => 30,
            'total_time' => 130,
        ])->create();
        $item_3 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::NO_ANSWER,
            'wait' => 30,
            'total_time' => 0,
        ])->create();
        $item_4 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::TRANSFER,
            'wait' => 20,
            'total_time' => 10,
        ])->create();
        $item_5 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::TRANSFER,
            'wait' => 230,
            'total_time' => 10,
        ])->create();
        $item_6 = $this->itemBuilder->setReport($report)->setData([
            'status' => ReportStatus::TRANSFER,
            'wait' => 20,
            'total_time' => 30,
        ])->create();

        $date = CarbonImmutable::now();
        /** @var $pause_item_1 PauseItem */
        $pause_item_1 = $this->pauseItemBuilder
            ->setReport($report)
            ->setPauseAt($date->subMinutes(30))
            ->setUnpauseAt($date->subMinutes(25))
            ->create();
        $pause_item_2 = $this->pauseItemBuilder
            ->setReport($report)
            ->setPauseAt($date->subMinutes(10))
            ->setUnpauseAt($date->subMinutes(8))
            ->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($report->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $report->id,
                                'calls' => 6,
                                'answered_calls' => 2,
                                'dropped_calls' => 1,
                                'transfer_calls' => 3,
                                'wait' => $item_1->wait + $item_2->wait + $item_3->wait + $item_4->wait + $item_5->wait + $item_6->wait,
                                'total_time' => $item_1->total_time + $item_2->total_time + $item_3->total_time + $item_4->total_time + $item_5->total_time + $item_6->total_time,
                                'pause' => 2,
                                'total_pause_time' => (5*60) + (2*60),
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
                %s (id: %s){
                    data {
                        id
                        calls
                        answered_calls
                        dropped_calls
                        transfer_calls
                        wait
                        total_time
                        pause
                        total_pause_time
                        employee {
                            id
                        }
                        items {
                            id
                            status
                            number
                            name
                            wait
                            total_time
                            call_at
                        }
                        pause_items {
                            id
                            pause_at
                            unpause_at
                            duration
                        }
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
    public function filter_by_department_id(): void
    {
        $this->loginAsSuperAdmin();

        $department_1 = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setDepartment($department_1)->create();
        $employee_2 = $this->employeeBuilder->setDepartment($department_1)->create();
        $employee_3 = $this->employeeBuilder->setDepartment($department_2)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();
        $report_3 = $this->reportBuilder->setEmployee($employee_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByDepartmentId($department_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $report_2->id,],
                            ['id' => $report->id,]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
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
                    data {
                        id
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
    public function filter_by_name(): void
    {
        $this->loginAsSuperAdmin();

        $search = 'test';

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setData([
            'first_name' => $search .'tt'
        ])->create();
        $employee_2 = $this->employeeBuilder->setData([
            'last_name' => $search . 'yuyu'
        ])->create();
        $employee_3 = $this->employeeBuilder->setData([
            'first_name' => 'rr',
            'last_name' => 'rr'
        ])->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();
        $report_3 = $this->reportBuilder->setEmployee($employee_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySearch($search)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $report_2->id,],
                            ['id' => $report->id,]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function filter_by_number(): void
    {
        $this->loginAsSuperAdmin();

        $sip_1 = $this->sipBuilder->setData(['number' => '309'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '308'])->create();
        $sip_3 = $this->sipBuilder->setData(['number' => '409'])->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->create();
        $employee_3 = $this->employeeBuilder->setSip($sip_3)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();
        $report_3 = $this->reportBuilder->setEmployee($employee_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySearch('30')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $report_2->id,],
                            ['id' => $report->id,]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrBySearch($value): string
    {
        return sprintf(
            '
            {
                %s (search: "%s"){
                    data {
                        id
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $value
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

        $date = CarbonImmutable::now();
        /** @var $pause_item_1 PauseItem */
        $pause_item_1 = $this->pauseItemBuilder
            ->setReport($report)
            ->setPauseAt($date->subMinutes(30))
            ->setUnpauseAt($date->subMinutes(25))
            ->create();

        $pause_item_2 = $this->pauseItemBuilder
            ->setReport($report)
            ->setPauseAt($date->subHours(100))
            ->setUnpauseAt($date->subHours(99))
            ->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByFromDate(
                $date->subDays(2)->format(DatetimeEnum::DEFAULT_FORMAT)
            )
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $report->id,
                                'calls' => 4,
                                'answered_calls' => 1,
                                'dropped_calls' => 1,
                                'transfer_calls' => 2,
                                'wait' => $item_2->wait + $item_3->wait + $item_4->wait + $item_6->wait,
                                'total_time' => $item_2->total_time + $item_3->total_time + $item_4->total_time + $item_6->total_time,
                                'pause' => 1,
                                'total_pause_time' => 5*60,
                                'items' => [
                                    ['id' => $item_2->id],
                                    ['id' => $item_3->id],
                                    ['id' => $item_4->id],
                                    ['id' => $item_6->id],
                                ],
                                'pause_items' => [
                                    ['id' => $pause_item_1->id],
                                ]
                            ]
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(4, 'data.'. self::QUERY .'.data.0.items')
            ->assertJsonCount(1, 'data.'. self::QUERY .'.data.0.pause_items')
        ;
    }

    protected function getQueryStrByFromDate($from): string
    {
        return sprintf(
            '
            {
                %s (date_from: "%s"){
                    data {
                        id
                        calls
                        answered_calls
                        dropped_calls
                        transfer_calls
                        wait
                        total_time
                        pause
                        total_pause_time
                        items {
                            id
                        }
                        pause_items {
                            id
                        }
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
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

        $date = CarbonImmutable::now();
        /** @var $pause_item_1 PauseItem */
        $pause_item_1 = $this->pauseItemBuilder
            ->setReport($report)
            ->setPauseAt($date->subMinutes(30))
            ->setUnpauseAt($date->subMinutes(25))
            ->create();
        $pause_item_2 = $this->pauseItemBuilder
            ->setReport($report)
            ->setPauseAt($date->subHours(100))
            ->setUnpauseAt($date->subHours(99))
            ->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByToDate(
                $date->subDays(3)->format(DatetimeEnum::DEFAULT_FORMAT)
            )
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $report->id,
                                'calls' => 2,
                                'answered_calls' => 1,
                                'dropped_calls' => 0,
                                'transfer_calls' => 1,
                                'wait' => $item_1->wait + $item_5->wait,
                                'total_time' => $item_1->total_time + $item_5->total_time,
                                'pause' => 1,
                                'total_pause_time' => 60*60,
                                'items' => [
                                    ['id' => $item_1->id],
                                    ['id' => $item_5->id],
                                ],
                                'pause_items' => [
                                    ['id' => $pause_item_2->id],
                                ]
                            ]
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'. self::QUERY .'.data.0.items')
            ->assertJsonCount(1, 'data.'. self::QUERY .'.data.0.pause_items')
        ;
    }

    protected function getQueryStrByToDate($to): string
    {
        return sprintf(
            '
            {
                %s (date_to: "%s"){
                    data {
                        id
                        calls
                        answered_calls
                        dropped_calls
                        transfer_calls
                        wait
                        total_time
                        pause
                        total_pause_time
                        items {
                            id
                        }
                        pause_items {
                            id
                        }
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $to
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

        $date = CarbonImmutable::now();
        /** @var $pause_item_1 PauseItem */
        $pause_item_1 = $this->pauseItemBuilder
            ->setReport($report)
            ->setPauseAt($date->subMinutes(30))
            ->setUnpauseAt($date->subMinutes(25))
            ->create();
        $pause_item_2 = $this->pauseItemBuilder
            ->setReport($report)
            ->setPauseAt($date->subHours(60))
            ->setUnpauseAt($date->subHours(58))
            ->create();
        $pause_item_3 = $this->pauseItemBuilder
            ->setReport($report)
            ->setPauseAt($date->subHours(50))
            ->setUnpauseAt($date->subHours(49))
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
                        'data' => [
                            [
                                'id' => $report->id,
                                'calls' => 4,
                                'answered_calls' => 1,
                                'dropped_calls' => 1,
                                'transfer_calls' => 2,
                                'wait' => $item_1->wait + $item_3->wait + $item_4->wait + $item_5->wait,
                                'total_time' => $item_1->total_time + $item_3->total_time + $item_4->total_time + $item_5->total_time,
                                'pause' => 2,
                                'total_pause_time' => (60*60)+((60*60)*2),
                                'items' => [
                                    ['id' => $item_1->id],
                                    ['id' => $item_3->id],
                                    ['id' => $item_4->id],
                                    ['id' => $item_5->id],
                                ],
                                'pause_items' => [
                                    ['id' => $pause_item_2->id],
                                    ['id' => $pause_item_3->id],
                                ]
                            ]
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(4, 'data.'. self::QUERY .'.data.0.items')
            ->assertJsonCount(2, 'data.'. self::QUERY .'.data.0.pause_items')
        ;
    }

    protected function getQueryStrByDate($from, $to): string
    {
        return sprintf(
            '
            {
                %s (date_from: "%s", date_to: "%s"){
                    data {
                        id
                        calls
                        answered_calls
                        dropped_calls
                        transfer_calls
                        wait
                        total_time
                        pause
                        total_pause_time
                        items {
                            id
                        }
                        pause_items {
                            id
                        }
                    }
                    meta {
                        total
                    }
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
