<?php

namespace Tests\Feature\Queries\BackOffice\Calls\Queue;

use App\Enums\Calls\QueueStatus;
use App\GraphQL\Queries\BackOffice;
use App\Models\Calls\History;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class QueuesListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Calls\Queue\QueuesListQuery::NAME;

    protected DepartmentBuilder $departmentBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected QueueBuilder $queueBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);
    }

    /** @test */
    public function success_list_as_super_admin(): void
    {
        $this->loginAsSuperAdmin();

        $m_1 = $this->queueBuilder->create();
        $m_2 = $this->queueBuilder->create();
        $m_3 = $this->queueBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $m_3->id],
                        ['id' => $m_2->id],
                        ['id' => $m_1->id],
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::QUERY)
        ;
    }

    /** @test */
    public function success_paginator_as_employee(): void
    {
        $this->loginAsEmployee();

        $m_1 = $this->queueBuilder->create();
        $m_2 = $this->queueBuilder->create();
        $m_3 = $this->queueBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $m_3->id],
                        ['id' => $m_2->id],
                        ['id' => $m_1->id],
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::QUERY)
        ;
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
                    self::QUERY => []
                ]
            ])
            ->assertJsonCount(0, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    id
                }
            }',
            self::QUERY
        );
    }

    /** @test */
    public function filter_by_id_wait_status(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Queue */
        $model = $this->queueBuilder
            ->setDepartment($department)
            ->setConnectionNum(Queue::UNKNOWN)
            ->setConnectionName(Queue::UNKNOWN)
            ->create();

        $this->queueBuilder->create();
        $this->queueBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        [
                            'id' => $model->id,
                            'department' => [
                                'id' => $department->id
                            ],
                            'employee' => null,
                            'from' => $model->caller_num,
                            'from_name' => $model->caller_name,
                            'status' => QueueStatus::WAIT,
                            'connected' => null,
                            'connected_name' => null,
                            'connected_at' => null,
                            'position' => $model->position,
                            'wait' => $model->wait,
                            'serial_number' => $model->serial_number,
                            'case_id' => $model->case_id,
                            'comment' => $model->comment,
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::QUERY)
        ;
    }

    /** @test */
    public function filter_by_id_connection_status(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();

        /** @var $model Queue */
        $model = $this->queueBuilder
            ->setDepartment($department)
            ->setEmployee($employee)
            ->setStatus(QueueStatus::CONNECTION())
            ->setConnectedAt(CarbonImmutable::now()->subMinutes(5))
            ->create();

        $this->queueBuilder->create();
        $this->queueBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByid($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        [
                            'id' => $model->id,
                            'department' => [
                                'id' => $department->id
                            ],
                            'employee' => [
                                'id' => $employee->id
                            ],
                            'from' => $model->caller_num,
                            'from_name' => $model->caller_name,
                            'status' => QueueStatus::CONNECTION,
                            'connected' => $model->connected_num,
                            'connected_name' => $model->connected_name,
                            'connected_at' => $model->connected_at,
                            'called_at' => $model->called_at,
                            'position' => $model->position,
                            'wait' => $model->wait,
                            'serial_number' => $model->serial_number,
                            'case_id' => $model->case_id,
                            'comment' => $model->comment,
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrById($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s){
                    id
                    department {
                        id
                    }
                    employee {
                        id
                    }
                    status
                    connected
                    connected_name
                    connected_at
                    from
                    from_name
                    position
                    wait
                    serial_number
                    case_id
                    comment
                    created_at
                    updated_at
                    called_at
                }
            }',
            self::QUERY,
            $id
        );
    }

    /** @test */
    public function filter_by_statuses(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Queue */
        $model = $this->queueBuilder
            ->setDepartment($department)
            ->setStatus(QueueStatus::CONNECTION())
            ->create();

        $model_2 = $this->queueBuilder->create();
        $model_3 = $this->queueBuilder->setStatus(QueueStatus::CANCEL())->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatuses([QueueStatus::WAIT, QueueStatus::CONNECTION])
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $model_2->id,],
                        ['id' => $model->id,],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrByStatuses(array $data): string
    {
        return sprintf(
            '
            {
                %s (statuses: [%s, %s]){
                    id
                }
            }',
            self::QUERY,
            data_get($data, '0'),
            data_get($data, '1'),
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
        $m_1 = $this->queueBuilder->setDepartment($department)->create();
        $m_2 = $this->queueBuilder->setDepartment($department)->create();
        $m_3 = $this->queueBuilder->setDepartment($department_2)->create();
        $this->queueBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByDepartment($department->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $m_2->id,],
                        ['id' => $m_1->id,],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrByDepartment($id): string
    {
        return sprintf(
            '
            {
                %s (department_id: %s){
                    id
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

        $sn = "98989";

        /** @var $m_1 History */
        $m_1 = $this->queueBuilder->setSerialNumber($sn . "DD78")->create();
        $m_2 = $this->queueBuilder->setSerialNumber("445" . $sn)->create();
        $m_3 = $this->queueBuilder->setSerialNumber("DD78")->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySerialNumber($sn)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $m_2->id,],
                        ['id' => $m_1->id,]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrBySerialNumber(string $value): string
    {
        return sprintf(
            '
            {
                %s (serial_number: "%s"){
                    id
                }
            }',
            self::QUERY,
            $value
        );
    }

    /** @test */
    public function search_by_case_id(): void
    {
        $this->loginAsSuperAdmin();

        $sn = "98989";

        /** @var $m_1 History */
        $m_1 = $this->queueBuilder->setCaseId($sn . "DD78")->create();
        $m_2 = $this->queueBuilder->setCaseId("445" . $sn)->create();
        $m_3 = $this->queueBuilder->setCaseId("DD78")->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByCaseId($sn)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $m_2->id,],
                        ['id' => $m_1->id,]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrByCaseId(string $value): string
    {
        return sprintf(
            '
            {
                %s (case_id: "%s"){
                    id
                }
            }',
            self::QUERY,
            $value
        );
    }

    /** @test */
    public function search(): void
    {
        $this->loginAsSuperAdmin();

        $sn = "tes";

        /** @var $m_1 History */
        $m_1 = $this->queueBuilder->setFromName($sn . "DD78")->create();
        $m_2 = $this->queueBuilder->setFromNum("+1676". $sn)->create();
        $m_3 = $this->queueBuilder->setComment("some ". $sn . " cpmment")->create();
        $m_4 = $this->queueBuilder->setFromName("445")->create();
        $m_5 = $this->queueBuilder->setFromNum("DD78")->create();
        $m_6 = $this->queueBuilder->setComment("DD78")->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySearch($sn)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $m_3->id,],
                        ['id' => $m_2->id,],
                        ['id' => $m_1->id,]
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrBySearch(string $value): string
    {
        return sprintf(
            '
            {
                %s (search: "%s"){
                    id
                }
            }',
            self::QUERY,
            $value
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
