<?php

namespace Tests\Feature\Queries\BackOffice\Employees;

use App\Enums\Employees\Status;
use App\GraphQL\Queries\BackOffice;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class EmployeesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Employees\EmployeesQuery::NAME;

    protected DepartmentBuilder $departmentBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
    }

    /** @test */
    public function success_paginator_as_super_admin(): void
    {
        $this->loginAsSuperAdmin();

        $d_1 = $this->departmentBuilder->create();
        $d_2 = $this->departmentBuilder->create();

        $m_1 = $this->employeeBuilder->setDepartment($d_1)->create();
        $m_2 = $this->employeeBuilder->setDepartment($d_1)->create();
        $m_3 = $this->employeeBuilder->setDepartment($d_2)->create();

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
        $d_1 = $this->departmentBuilder->create();
        $d_2 = $this->departmentBuilder->create();

        $m_1 = $this->employeeBuilder->setDepartment($d_1)->create();
        $m_2 = $this->employeeBuilder->setDepartment($d_1)->create();
        $m_3 = $this->employeeBuilder->setDepartment($d_2)->create();

        $this->loginAsEmployee($m_3);

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

        $this->employeeBuilder->create();
        $this->employeeBuilder->create();
        $this->employeeBuilder->create();

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

        $this->employeeBuilder->create();
        $this->employeeBuilder->create();
        $this->employeeBuilder->create();

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
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder
            ->setSip($sip)
            ->setDepartment($department)
            ->create();
        $this->employeeBuilder->create();
        $this->employeeBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJsonStructure([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'created_at',
                                'updated_at',
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $model->id,
                                'status' => $model->status,
                                'first_name' => $model->first_name,
                                'last_name' => $model->last_name,
                                'email' => $model->email,
                                'department' => [
                                    'id' => $department->id
                                ],
                                'sip' => [
                                    'id' => $sip->id
                                ],
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
                        first_name
                        last_name
                        email
                        department {
                            id
                        }
                        sip {
                            id
                        }
                        created_at
                        updated_at
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
    public function filter_by_status(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Employee */
        $model = $this->employeeBuilder->setStatus(Status::ERROR())->create();
        $model_2 = $this->employeeBuilder->setStatus(Status::ERROR())->create();
        $this->employeeBuilder->setStatus(Status::TALK())->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatus(Status::ERROR())
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $model_2->id],
                            ['id' => $model->id],
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByStatus(Status $status): string
    {
        return sprintf(
            '
            {
                %s (status: %s){
                    data {
                        id
                        status
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY,
            $status
        );
    }

    /** @test */
    public function filter_by_department(): void
    {
        $this->loginAsSuperAdmin();

        $d_1 = $this->departmentBuilder->create();
        $d_2 = $this->departmentBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->setDepartment($d_1)->create();
        $model_2 = $this->employeeBuilder->setDepartment($d_1)->create();
        $this->employeeBuilder->setDepartment($d_2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByDepartment($d_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $model_2->id],
                            ['id' => $model->id],
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
    public function filter_by_sip(): void
    {
        $this->loginAsSuperAdmin();

        $s_1 = $this->sipBuilder->create();
        $s_2 = $this->sipBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->setSip($s_1)->create();
        $model_2 = $this->employeeBuilder->setSip($s_2)->create();
        $this->employeeBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySip($s_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $model->id],
                        ],
                        'meta' => [
                            'total' => 1
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrBySip($id): string
    {
        return sprintf(
            '
            {
                %s (sip_id: %s){
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
    public function search_name_or_email(): void
    {
        $this->loginAsSuperAdmin();

        $search = 'test';
        /** @var $model Employee */
        $model_1 = $this->employeeBuilder->setData([
            'first_name' => $search . 'ert'
        ])->create();
        $model_2 = $this->employeeBuilder->setData([
            'last_name' => $search . 'ertrtr'
        ])->create();
        $model_3 = $this->employeeBuilder->setData([
            'email' => 'jj' . $search . 'ertrtr@gmail.com'
        ])->create();
        $this->employeeBuilder->create();
        $this->employeeBuilder->create();
        $this->employeeBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySearch($search)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $model_3->id],
                            ['id' => $model_2->id],
                            ['id' => $model_1->id],
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
    public function search_only_name(): void
    {
        $this->loginAsSuperAdmin();

        $search = 'test up';
        /** @var $model Employee */
        $model_1 = $this->employeeBuilder->setData([
            'first_name' => 'testrrr',
            'last_name' => 'rruprr'
        ])->create();
        $model_2 = $this->employeeBuilder->setData([
            'first_name' => $search . 'ertest'
        ])->create();
        $model_3 = $this->employeeBuilder->setData([
            'last_name' => 'eruppp'
        ])->create();
        $this->employeeBuilder->create();
        $this->employeeBuilder->create();
        $this->employeeBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrBySearch($search)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $model_3->id],
                            ['id' => $model_2->id],
                            ['id' => $model_1->id],
                        ],
                        'meta' => [
                            'total' => 3
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
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
        ;

        $this->assertPermission($res);
    }

//    /** @test */
//    public function not_auth(): void
//    {
//        $res = $this->postGraphQLBackOffice([
//            'query' => $this->getQueryStr()
//        ])
//        ;
//
//        $this->assertUnauthorized($res);
//    }
}
