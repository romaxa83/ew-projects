<?php

namespace Tests\Unit\Models\Employees;

use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use DatabaseTransactions;

    protected EmployeeBuilder $employeeBuilder;
    public function setUp(): void
    {
        $this->employeeBuilder = resolve(EmployeeBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_is_online_as_ringostat_not_zadarma()
    {
        /** @var $model Employee */
        $model = $this->employeeBuilder
            ->ringostat_sip_status(true)
            ->zadarma_sip_status(false)
            ->create();

        $this->assertTrue($model->isOnline());
    }

    /** @test */
    public function check_is_online_as_zadarma_not_ringostat()
    {
        /** @var $model Employee */
        $model = $this->employeeBuilder
            ->ringostat_sip_status(false)
            ->zadarma_sip_status(true)
            ->create();

        $this->assertTrue($model->isOnline());
    }

    /** @test */
    public function check_is_not_online_as_ringostat_and_zadarma()
    {
        /** @var $model Employee */
        $model = $this->employeeBuilder
            ->ringostat_sip_status(false)
            ->zadarma_sip_status(false)
            ->create();

        $this->assertFalse($model->isOnline());
    }
}
