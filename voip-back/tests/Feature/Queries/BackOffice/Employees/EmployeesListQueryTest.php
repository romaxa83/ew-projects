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

class EmployeesListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Employees\EmployeesListQuery::NAME;

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
    public function success_list_as_super_admin(): void
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
            ->assertJson([
                'data' => [
                    self::QUERY => [
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
        $this->employeeBuilder->setStatus(Status::FREE())->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatus(Status::ERROR())
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $model_2->id],
                        ['id' => $model->id],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrByStatus(Status $status): string
    {
        return sprintf(
            '
            {
                %s (status: %s){
                    id
                }
            }',
            self::QUERY,
            $status
        );
    }

    /** @test */
    public function filter_by_statuses(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Employee */
        $model = $this->employeeBuilder->setStatus(Status::ERROR())->create();
        $model_2 = $this->employeeBuilder->setStatus(Status::ERROR())->create();
        $model_3 = $this->employeeBuilder->setStatus(Status::FREE())->create();
        $model_4 = $this->employeeBuilder->setStatus(Status::TALK())->create();
        $model_5 = $this->employeeBuilder->setStatus(Status::PAUSE())->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatuses([Status::TALK, Status::PAUSE])
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $model_5->id],
                        ['id' => $model_4->id],
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
            $data[0],
            $data[1],
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
                        ['id' => $model_2->id],
                        ['id' => $model->id],
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
                        ['id' => $model->id],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrBySip($id): string
    {
        return sprintf(
            '
            {
                %s (sip_id: %s){
                    id
                }
            }',
            self::QUERY,
            $id
        );
    }

    /** @test */
    public function filter_has_sip(): void
    {
        $this->loginAsSuperAdmin();

        $s_1 = $this->sipBuilder->create();
        $s_2 = $this->sipBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->setSip($s_1)->create();
        $model_2 = $this->employeeBuilder->setSip($s_2)->create();
        $model_3 = $this->employeeBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrHasSip('true')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $model_2->id],
                        ['id' => $model->id],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrHasSip($value): string
    {
        return sprintf(
            '
            {
                %s (has_sip: %s){
                    id
                }
            }',
            self::QUERY,
            $value
        );
    }

    /** @test */
    public function filter_has_active_department(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $d_1 Department */
        $d_1 = $this->departmentBuilder->setData(['active' => true])->create();
        $d_2 = $this->departmentBuilder->setData(['active' => false])->create();
        $d_3 = $this->departmentBuilder->setData(['active' => true])->create();

        /** @var $e_1 Employee */
        $e_1 = $this->employeeBuilder->setDepartment($d_1)->create();
        $e_2 = $this->employeeBuilder->setDepartment($d_2)->create();
        $e_3 = $this->employeeBuilder->setDepartment($d_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrHasActiveDepartment('true')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $e_3->id],
                        ['id' => $e_1->id],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrHasActiveDepartment($value): string
    {
        return sprintf(
            '
            {
                %s (has_active_department: %s){
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

