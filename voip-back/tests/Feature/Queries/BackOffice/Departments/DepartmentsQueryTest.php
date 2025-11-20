<?php

namespace Tests\Feature\Queries\BackOffice\Departments;

use App\GraphQL\Queries\BackOffice;
use App\Models\Departments\Department;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class DepartmentsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Departments\DepartmentsQuery::NAME;

    protected DepartmentBuilder $departmentBuilder;
    protected EmployeeBuilder $employeeBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        $m_1 = $this->departmentBuilder->setData(['active' => true])->create();
        $m_2 = $this->departmentBuilder->setData(['active' => true])->create();
        $m_3 = $this->departmentBuilder->setData(['active' => false])->create();

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
        $m_1 = $this->departmentBuilder->create();
        $m_2 = $this->departmentBuilder->create();
        $m_3 = $this->departmentBuilder->create();

        $employee = $this->employeeBuilder->setDepartment($m_3)->create();

        $this->loginAsEmployee($employee);

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

        $this->departmentBuilder->create();
        $this->departmentBuilder->create();
        $this->departmentBuilder->create();

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

        $this->departmentBuilder->create();
        $this->departmentBuilder->create();
        $this->departmentBuilder->create();

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

        /** @var $model Department */
        $model = $this->departmentBuilder->create();
        $model_1 = $this->departmentBuilder->create();
        $this->departmentBuilder->create();

        $this->employeeBuilder->setDepartment($model)->create();
        $this->employeeBuilder->setDepartment($model)->create();
        $this->employeeBuilder->setDepartment($model)->create();
        $this->employeeBuilder->setDepartment($model_1)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $model->id,
                                'name' => $model->name,
                                'sort' => $model->sort,
                                'active' => $model->active,
                                'employees_count' => $model->employees()->count(),
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
                        name
                        sort
                        active
                        employees_count
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
    public function filter_by_active(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $d_1 Department */
        $d_1 = $this->departmentBuilder->setData(['active' => true])->create();
        $d_2 = $this->departmentBuilder->setData(['active' => true])->create();
        $d_3 = $this->departmentBuilder->setData(['active' => false])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByActive('true')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['id' => $d_2->id],
                            ['id' => $d_1->id],
                        ],
                        'meta' => [
                            'total' => 2
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrByActive($value): string
    {
        return sprintf(
            '
            {
                %s (active: %s){
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
