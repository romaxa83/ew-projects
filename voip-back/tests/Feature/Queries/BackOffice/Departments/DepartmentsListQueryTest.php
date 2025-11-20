<?php

namespace Tests\Feature\Queries\BackOffice\Departments;

use App\GraphQL\Queries\BackOffice;
use App\Models\Departments\Department;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class DepartmentsListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Departments\DepartmentsListQuery::NAME;

    protected DepartmentBuilder $departmentBuilder;
    protected EmployeeBuilder $employeeBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function success_list(): void
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
    public function success_list_as_employee(): void
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
    public function filter_by_id(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Department */
        $model = $this->departmentBuilder->create();
        $this->departmentBuilder->create();
        $this->departmentBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        [
                            'id' => $model->id,
                            'name' => $model->name,
                            'sort' => $model->sort,
                            'active' => $model->active,
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
                    name
                    sort
                    active
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
        $d_2 = $this->departmentBuilder->setData(['active' => false])->create();
        $d_3 = $this->departmentBuilder->setData(['active' => true])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByActive('true')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $d_3->id],
                        ['id' => $d_1->id],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrByActive($value): string
    {
        return sprintf(
            '
            {
                %s (active: %s){
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
