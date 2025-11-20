<?php

namespace Tests\Feature\Queries\BackOffice\Sips;

use App\Enums\Employees\Status;
use App\GraphQL\Queries\BackOffice;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class SipsListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Sips\SipsListQuery::NAME;

    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function success_list(): void
    {
        $this->loginAsSuperAdmin();

        $m_1 = $this->sipBuilder->create();
        $m_2 = $this->sipBuilder->create();
        $m_3 = $this->sipBuilder->create();

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
        $this->loginAsEmployee();

        $m_1 = $this->sipBuilder->create();
        $m_2 = $this->sipBuilder->create();
        $m_3 = $this->sipBuilder->create();

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

        /** @var $model Sip */
        $model = $this->sipBuilder->create();
        $this->sipBuilder->create();
        $this->sipBuilder->create();

        $employee = $this->employeeBuilder->setSip($model)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        [
                            'id' => $model->id,
                            'number' => $model->number,
                            'employee' => [
                                'id' => $employee->id
                            ],
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrById($value): string
    {
        return sprintf(
            '
            {
                %s (id: "%s"){
                     id
                     number
                     employee {
                        id
                     }
                }
            }',
            self::QUERY,
            $value
        );
    }

    /** @test */
    public function filter_by_employee_statuses(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $s-1 Sip */
        $s_1 = $this->sipBuilder->create();
        $s_2 = $this->sipBuilder->create();
        $s_3 = $this->sipBuilder->create();

        /** @var $e_1 Employee */
        $e_1 = $this->employeeBuilder->setSip($s_1)->setStatus(Status::FREE())->create();
        $e_2 = $this->employeeBuilder->setSip($s_2)->setStatus(Status::TALK())->create();
        $e_3 = $this->employeeBuilder->setSip($s_3)->setStatus(Status::PAUSE())->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByEmployeeStatuses([
                Status::FREE(),
                Status::PAUSE(),
            ])
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $s_3->id],
                        ['id' => $s_1->id]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrByEmployeeStatuses(array $value): string
    {
        return sprintf(
            '
            {
                %s (employee_statuses: [%s, %s]){
                     id
                }
            }',
            self::QUERY,
            $value[0],
            $value[1],
        );
    }

    /** @test */
    public function filter_by_has_employee(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();
        $model_2 = $this->sipBuilder->create();
        $model_3 = $this->sipBuilder->create();
        $model_4 = $this->sipBuilder->create();

        $this->employeeBuilder->setSip($model)->create();
        $this->employeeBuilder->setSip($model_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByHasEmployee('true')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $model_3->id],
                        ['id' => $model->id]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    /** @test */
    public function filter_by_doesnt_have_employee(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();
        $model_2 = $this->sipBuilder->create();
        $model_3 = $this->sipBuilder->create();
        $model_4 = $this->sipBuilder->create();

        $this->employeeBuilder->setSip($model)->create();
        $this->employeeBuilder->setSip($model_3)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByHasEmployee('false')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $model_4->id],
                        ['id' => $model_2->id]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrByHasEmployee($value): string
    {
        return sprintf(
            '
            {
                %s (has_employee: %s){
                    id
                    number
                    employee {
                        id
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

