<?php

namespace Tests\Feature\Queries\BackOffice\Calls\History;

use App\Enums\Calls\HistoryStatus;
use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Queries\BackOffice;
use App\Models\Calls\History;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Calls\HistoryBuilder;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class HistoriesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Calls\History\HistoriesQuery::NAME;

    protected DepartmentBuilder $departmentBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected HistoryBuilder $historyBuilder;
    protected QueueBuilder $queueBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        $m_1 = $this->historyBuilder->create();
        $m_2 = $this->historyBuilder->create();
        $m_3 = $this->historyBuilder->create();

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
    public function success_paginator_via_employee(): void
    {
        $employee = $this->loginAsEmployee();

        $m_1 = $this->historyBuilder->setEmployee($employee)->create();
        $m_2 = $this->historyBuilder->setEmployee($employee)->create();
        $m_3 = $this->historyBuilder->setFromEmployee($employee)->create();
        $m_4 = $this->historyBuilder->create();

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
    public function success_with_page(): void
    {
        $this->loginAsSuperAdmin();

        $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();

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

        $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();

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

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();

        $channel = 'PJSIP/kamailio-00000eef';

        /** @var $queue Queue */
        $queue = $this->queueBuilder->setChannel($channel)->create();

        /** @var $model History */
        $model = $this->historyBuilder->setDepartment($department)
            ->setEmployee($employee)->setChannel($channel)->create();

        $this->historyBuilder->create();
        $this->historyBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $model->id,
                                'status' => $model->status,
                                'department' => [
                                    'id' => $department->id
                                ],
                                'employee' => [
                                    'id' => $employee->id
                                ],
                                'from_num' => $model->from_num,
                                'from_name' => $model->from_name_pretty,
                                'dialed' => $model->dialed,
                                'dialed_name' => $model->dialed_name,
                                'duration' => $model->duration,
                                'billsec' => $model->billsec,
                                'serial_numbers' => $model->serial_numbers,
                                'case_id' => $model->case_id,
                                'comment' => $model->comment,
                                'call_date' => $model->call_date,
                                'call_record_link' => $model->getUrlAudioRecord(),
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
                        status
                        department {
                            id
                        }
                        employee {
                            id
                        }
                        from_num
                        from_name
                        dialed
                        dialed_name
                        duration
                        billsec
                        serial_numbers
                        case_id
                        comment
                        call_date
                        created_at
                        updated_at
                        call_record_link
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
    public function filter_by_department(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setDepartment($department)->create();
        $m_2 = $this->historyBuilder->setDepartment($department)->create();
        $m_3 = $this->historyBuilder->setDepartment($department_2)->create();
        $this->historyBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByDepartment($department->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_2->id,],
                            ['id' => $m_1->id,]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByDepartment($id): string
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
    public function search_by_serial_number(): void
    {
        $this->loginAsSuperAdmin();

        $serialNumber = '34RTCP';

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setData(['serial_numbers' => $serialNumber . '7878'])->create();
        $m_2 = $this->historyBuilder->setData(['serial_numbers' => 'RRT'. $serialNumber . '7878'])->create();
        $m_3 = $this->historyBuilder->create();
        $this->historyBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySerialNumber($serialNumber)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_2->id,],
                            ['id' => $m_1->id,]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrBySerialNumber(string $value): string
    {
        return sprintf(
            '
            {
                %s (serial_number: "%s"){
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
    public function search_by_name(): void
    {
        $this->loginAsSuperAdmin();

        $search = 'term';

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder
            ->setData([
                'from_name_pretty' => $search . '7878',
                'serial_numbers' => '4444',
                'case_id' => '4444',
            ])->create();
        $m_2 = $this->historyBuilder
            ->setData([
                'dialed_name' => '7878' . $search,
                'dialed' => '444',
                'case_id' => '4444',
            ])->create();
        $m_3 = $this->historyBuilder
            ->setData([
                'from_name_pretty' => '7878',
                'dialed' => '444',
                'serial_numbers' => '4444',
                'case_id' => '4444',
            ])->create();
        $m_4 = $this->historyBuilder
            ->setData([
                'from_name_pretty' => '7878',
                'dialed' => '444',
                'serial_numbers' => '4444',
                'case_id' => '4444',
            ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySearch($search)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_2->id,],
                            ['id' => $m_1->id,]
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
    public function search_by_name_and_serial_number(): void
    {
        $this->loginAsSuperAdmin();

        $search = 'test';
        $serialNumber = '11T22';

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder
            ->setData([
                'from_name_pretty' => $search . '7878',
                'serial_numbers' => '4444',
                'case_id' => '4444',
            ])->create();
        $m_2 = $this->historyBuilder
            ->setData([
                'from_name_pretty' => '7878' . $search,
                'dialed' => '444',
                'serial_numbers' => $serialNumber .'RRR',
                'case_id' => '4444',
            ])->create();
        $m_3 = $this->historyBuilder
            ->setData([
                'from_name_pretty' => '7878' . $search,
                'dialed' => '444',
                'serial_numbers' => '4444',
                'case_id' => '4444',
            ])->create();
        $m_4 = $this->historyBuilder
            ->setData([
                'from_name_pretty' => '7878',
                'dialed' => '444',
                'serial_numbers' => '4444',
                'case_id' => '4444',
            ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySearchAndSerialNumber($search, $serialNumber)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_2->id,],
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
    public function search_by_phone(): void
    {
        $this->loginAsSuperAdmin();

        $search = '390';

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder
            ->setData([
                'from_num' => $search . '7878',
                'dialed' => '4444',
                'comment' => '4444',
            ])->create();
        $m_2 = $this->historyBuilder
            ->setData([
                'from_num' => '7878',
                'dialed' => '4444'. $search,
                'comment' => '4444',
            ])->create();
        $m_3 = $this->historyBuilder
            ->setData([
                'from_num' => '7878',
                'dialed' => '4444',
                'comment' => 'comment ' . $search . ' test',
            ])->create();
        $m_4 = $this->historyBuilder
            ->setData([
                'from_num' => '7878',
                'dialed' => '4444',
                'comment' => '4444',
            ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySearch($search)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_3->id,],
                            ['id' => $m_2->id,],
                            ['id' => $m_1->id,]
                        ],
                        'meta' => [
                            'total' => 3
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrBySearch(string $value): string
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

    protected function getQueryStrBySearchAndSerialNumber(string $search, string $serialNumber): string
    {
        return sprintf(
            '
            {
                %s (search: "%s", serial_number: "%s"){
                    data {
                        id
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $search,
            $serialNumber
        );
    }

    /** @test */
    public function search_by_case_id(): void
    {
        $this->loginAsSuperAdmin();

        $caseID = '34';

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setData(['case_id' => $caseID . '78'])->create();
        $m_2 = $this->historyBuilder->setData(['case_id' => '66'. $caseID . '7878'])->create();
        $m_3 = $this->historyBuilder->setData(['case_id' => '7878'])->create();
        $this->historyBuilder->setData(['case_id' => '78'])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByCaseID($caseID)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_2->id,],
                            ['id' => $m_1->id,]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByCaseID(string $value): string
    {
        return sprintf(
            '
            {
                %s (case_id: "%s"){
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
    public function filter_by_status(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setStatus(HistoryStatus::BUSY())->create();
        $m_2 = $this->historyBuilder->setStatus(HistoryStatus::BUSY())->create();
        $m_3 = $this->historyBuilder->setStatus(HistoryStatus::CANCEL())->create();
        $this->historyBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatus(HistoryStatus::BUSY)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_2->id,],
                            ['id' => $m_1->id,]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByStatus($value): string
    {
        return sprintf(
            '
            {
                %s (status: %s){
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

        $date = CarbonImmutable::now();

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(3)
        ])->create();
        $m_2 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(1)
        ])->create();
        $m_3 = $this->historyBuilder->setData([
            'call_date' => $date->subDays(2)
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByFromDate($date->subDays(1)->format(DatetimeEnum::DEFAULT_FORMAT))
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_2->id,],
                            ['id' => $m_1->id,]
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByFromDate($value): string
    {
        return sprintf(
            '
            {
                %s (date_from: "%s"){
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
    public function filter_by_to_date(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(3)
        ])->create();
        $m_2 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(1)
        ])->create();
        $m_3 = $this->historyBuilder->setData([
            'call_date' => $date->subDays(2)
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByToDate($date->format(DatetimeEnum::DEFAULT_FORMAT))
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_3->id,],
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByToDate($value): string
    {
        return sprintf(
            '
            {
                %s (date_to: "%s"){
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
    public function filter_by_from_and_to_date(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(3)
        ])->create();
        $m_2 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(1)
        ])->create();
        $m_3 = $this->historyBuilder->setData([
            'call_date' => $date->subDays(2)
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByFromAndToDate(
                $date->format(DatetimeEnum::DEFAULT_FORMAT),
                $date->addDays(2)->format(DatetimeEnum::DEFAULT_FORMAT),
            )
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_2->id,],
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByFromAndToDate($from, $to): string
    {
        return sprintf(
            '
            {
                %s (
                    date_from: "%s"
                    date_to: "%s"
                ){
                    data {
                        id
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $from,
            $to
        );
    }

    /** @test */
    public function sort_by_to_call_date_asc(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(1)
        ])->create();
        $m_2 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(2)
        ])->create();
        $m_3 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(3)
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrSort('call_date-asc')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_1->id,],
                            ['id' => $m_2->id,],
                            ['id' => $m_3->id,],
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
    public function sort_by_to_call_date_desc(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(1)
        ])->create();
        $m_2 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(2)
        ])->create();
        $m_3 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(3)
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrSort('call_date-desc')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $m_3->id,],
                            ['id' => $m_2->id,],
                            ['id' => $m_1->id,],
                        ],
                        'meta' => [
                            'total' => 3
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrSort($value): string
    {
        return sprintf(
            '
            {
                %s (sort: "%s"){
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
    public function fail_filter_by_date_wrong_format(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(3)
        ])->create();
        $m_2 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(1)
        ])->create();
        $m_3 = $this->historyBuilder->setData([
            'call_date' => $date->subDays(2)
        ])->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByFromDate(
                $date->subDays(1)->format('Y/m/d')
            )
        ])
        ;

        $field = 'date_from';
        $this->assertResponseHasValidationMessage($res, $field, [
            __('validation.date_format', [
                'attribute' => 'date from',
                'format' => DatetimeEnum::DEFAULT_FORMAT,
            ])
        ]);
    }
    /** @test */
    public function fail_filter_by_from_date_wrong_format(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $m_1 History */
        $m_1 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(3)
        ])->create();
        $m_2 = $this->historyBuilder->setData([
            'call_date' => $date->addDays(1)
        ])->create();
        $m_3 = $this->historyBuilder->setData([
            'call_date' => $date->subDays(2)
        ])->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByFromDate($date->subDays(1)->format(DatetimeEnum::DATE))
        ])
        ;

        $field = 'date_from';
        $this->assertResponseHasValidationMessage($res, $field, [
            __('validation.date_format', [
                'attribute' => 'date from',
                'format' => DatetimeEnum::DEFAULT_FORMAT,
            ])
        ]);
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
