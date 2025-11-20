<?php

namespace Tests\Feature\Queries\BackOffice\Auth\Employee;

use App\GraphQL\Queries\BackOffice\Auth\Employee\EmployeeProfileQuery;
use App\Models\Employees\Employee;
use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class EmployeeProfileQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = EmployeeProfileQuery::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
    }

    /** @test */
    public function employee_profile(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->loginAsEmployee($employee);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $employee->id,
                        'email' => $employee->email->getValue(),
                        'status' => $employee->status,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'department' => [
                            'id' => $employee->department_id
                        ],
                        'sip' => [
                            'id' => $sip->id
                        ],
                        'roles' => [
                            ['name' => Role::employeeName()]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(
                Permission::query()->where('guard_name', Employee::GUARD)->count(),
                'data.' . self::QUERY . '.permissions'
            )
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    id
                    email
                    status
                    first_name
                    last_name
                    sip {
                        id
                    }
                    department {
                        id
                    }
                    roles {
                        name
                    }
                    permissions {
                        name
                    }
                }
            }',
            self::QUERY
        );
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
