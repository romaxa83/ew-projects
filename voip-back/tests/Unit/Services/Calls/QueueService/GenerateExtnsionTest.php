<?php

namespace Tests\Unit\Services\Calls\QueueService;

use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use App\Services\Calls\QueueService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;
use Tests\Traits\Ari\ChannelHelper;

class GenerateExtnsionTest extends TestCase
{
    use DatabaseTransactions;
    use ChannelHelper;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected QueueBuilder $queueBuilder;
    protected QueueService $queueService;
    protected function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve( SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);

        $this->queueService = resolve(QueueService::class);
    }

    /** @test */
    public function generate_extension_as_admin(): void
    {
        $admin = $this->loginAsSuperAdmin();

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $str = $this->queueService->generateExtension($admin, $employee);

        $this->assertEquals(
            $str,
            "{$employee->sip->number}_admin"
        );
    }

    /** @test */
    public function generate_extension_as_employee_and_not_sip(): void
    {
        $auth = $this->loginAsEmployee();

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $str = $this->queueService->generateExtension($auth, $employee);

        $this->assertEquals(
            $str,
            "{$employee->sip->number}_{$auth->guid}"
        );
    }

    /** @test */
    public function generate_extension_as_employee_has_sip(): void
    {
        $sip_auth = $this->sipBuilder->create();
        $auth = $this->employeeBuilder->setSip($sip_auth)->create();
        $this->loginAsEmployee($auth);

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $str = $this->queueService->generateExtension($auth, $employee);

        $this->assertEquals(
            $str,
            "{$employee->sip->number}_{$sip_auth->number}"
        );
    }

    /** @test */
    public function generate_extension_as_admin_for_department(): void
    {
        $auth = $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->setData([
            'num' => '67676'
        ])->create();

        $str = $this->queueService->generateExtension($auth, $department);

        $this->assertEquals(
            $str,
            "{$department->num}_admin"
        );
    }

    /** @test */
    public function generate_extension_as_employee_not_sip(): void
    {
        $auth = $this->loginAsEmployee();

        /** @var $department Department */
        $department = $this->departmentBuilder->setData([
            'num' => '67676'
        ])->create();

        $str = $this->queueService->generateExtension($auth, $department);

        $this->assertEquals(
            $str,
            "{$department->num}_{$auth->guid}"
        );
    }
}
