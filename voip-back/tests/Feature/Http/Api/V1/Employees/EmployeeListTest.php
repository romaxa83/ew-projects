<?php

namespace Tests\Feature\Http\Api\V1\Employees;

use App\Models\Employees\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class EmployeeListTest extends TestCase
{
    use DatabaseTransactions;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected DepartmentBuilder $departmentBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function get_list()
    {
        $this->employeeBuilder->create();
        $this->employeeBuilder->create();

        $this->get(
            route('api.v1.employees'),
            $this->headers()
        )
            ->assertOk()
            ->assertJsonStructure(['data' => [ 0 => [
                'id',
            ]]])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function get_list_assert_data()
    {
        $sip = $this->sipBuilder->create();
        $department = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setDepartment($department)
            ->setSip($sip)->create();

        $this->get(
            route('api.v1.employees'),
            $this->headers()
        )
            ->assertOk()
            ->assertJson(['data' => [
                [
                    'id' => $employee->id,
                    'guid' => $employee->guid,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'email' => $employee->email,
                    'status' => $employee->status,
                    'sip' => [
                        'id' => $employee->sip->id,
                        'number' => $employee->sip->number,
                    ],
                    'department' => [
                        'id' => $employee->department->id,
                        'guid' => $employee->department->guid,
                        'name' => $employee->department->name,
                        'number' => $employee->department->num
                    ]
                ]
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function get_empty_list()
    {
        $this->get(
            route('api.v1.employees'),
            $this->headers()
        )
            ->assertOk()
            ->assertJsonStructure(['data' => []])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function wrong_auth_token()
    {
        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $res = $this->get(
            route('api.v1.employees'),
            $headers
        )
            ->assertStatus(401)
        ;

        self::assertApiMsgError($res, 'Bad authorization token');
    }

    /** @test */
    public function without_auth_token()
    {
        $res = $this->get(
            route('api.v1.employees'),
            []
        )
            ->assertStatus(401)
        ;

        self::assertApiMsgError($res, 'Missing authorization header');
    }
}
