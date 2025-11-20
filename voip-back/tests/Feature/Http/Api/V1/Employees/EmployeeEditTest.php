<?php

namespace Tests\Feature\Http\Api\V1\Employees;

use App\Enums\Employees\Status;
use App\Models\Employees\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class EmployeeEditTest extends TestCase
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
    public function success_edit_status()
    {
        $sip = $this->sipBuilder->create();
        $department = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setDepartment($department)
            ->setStatus(Status::PAUSE())
            ->setSip($sip)->create();

        $data = [
            'status' => Status::TALK
        ];

        $this->assertNotEquals(data_get($data, 'status'), $employee->status);

        $this->post(
            route('api.v1.employees.edit', ['id' => $employee->id]),
            $data,
            $this->headers()
        )
            ->assertOk()
            ->assertJson(['data' => [
                'id' => $employee->id,
                'status' => data_get($data, 'status'),
            ]])
        ;
    }

    /** @test */
    public function fail_wrong_status()
    {
        $sip = $this->sipBuilder->create();
        $department = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setDepartment($department)
            ->setStatus(Status::PAUSE())
            ->setSip($sip)->create();

        $data = [
            'status' => 'talke'
        ];

        $res = $this->post(
            route('api.v1.employees.edit', ['id' => $employee->id]),
            $data,
            $this->headers()
        )
            ->assertStatus(400)
        ;

        self::assertApiMsgError($res, "The selected status is invalid.");
    }

    /** @test */
    public function fail_not_found()
    {
        $sip = $this->sipBuilder->create();
        $department = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setDepartment($department)
            ->setStatus(Status::PAUSE())
            ->setSip($sip)->create();

        $data = [
            'status' => Status::TALK
        ];

        $wrongID = $employee->id + 1;

        $res = $this->post(
            route('api.v1.employees.edit', ['id' => $wrongID]),
            $data,
            $this->headers()
        )
            ->assertNotFound()
        ;

        self::assertApiMsgError($res, "Employee not found by id - [{$wrongID}]");
    }

    /** @test */
    public function wrong_auth_token()
    {
        $sip = $this->sipBuilder->create();
        $department = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setDepartment($department)
            ->setStatus(Status::PAUSE())
            ->setSip($sip)->create();

        $data = [
            'status' => Status::TALK
        ];

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $res = $this->post(
            route('api.v1.employees.edit', ['id' => $employee->id]),
            $data,
            $headers
        )
            ->assertUnauthorized()
        ;

        self::assertApiMsgError($res, 'Bad authorization token');
    }

    /** @test */
    public function without_auth_token()
    {
        $sip = $this->sipBuilder->create();
        $department = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setDepartment($department)
            ->setStatus(Status::PAUSE())
            ->setSip($sip)->create();

        $data = [
            'status' => Status::TALK
        ];

        $res = $this->post(
            route('api.v1.employees.edit', ['id' => $employee->id]),
            $data
        )
            ->assertUnauthorized()
        ;

        self::assertApiMsgError($res, 'Missing authorization header');
    }
}

