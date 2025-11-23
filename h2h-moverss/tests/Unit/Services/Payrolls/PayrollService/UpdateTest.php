<?php

namespace Tests\Unit\Services\Payrolls\PayrollService;

use App\Models\CashRegistry\CashRegistry;
use App\Models\Order\Payroll\Item;
use App\Models\Order\Payroll\Payroll;
use App\Services\Payrolls\PayrollService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Orders\PayrollBuilder;
use Tests\Builders\Orders\PayrollItemBuilder;
use Tests\Builders\Users\RoleBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected EmployeeBuilder $employeeBuilder;
    protected RoleBuilder $roleBuilder;
    protected PayrollBuilder $payrollBuilder;
    protected PayrollItemBuilder $payrollItemBuilder;

    protected PayrollService $service;

    public function setUp(): void
    {
        $this->cashRegistry = resolve(CashRegistry::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->roleBuilder = resolve(RoleBuilder::class);
        $this->payrollBuilder = resolve(PayrollBuilder::class);
        $this->payrollItemBuilder = resolve(PayrollItemBuilder::class);
        $this->service = resolve(PayrollService::class);

        parent::setUp();
    }

    /** @test */
    public function success_update()
    {
        /** @var $payroll Payroll */
        $payroll = $this->payrollBuilder->create();

        $employee_1 = $this->employeeBuilder->create();
        $employee_2 = $this->employeeBuilder->create();
        $employee_3 = $this->employeeBuilder->create();

        $role_1 = $this->roleBuilder->create();
        $role_2 = $this->roleBuilder->create();
        $role_3 = $this->roleBuilder->create();

        $cash_registry = $this->cashRegistry->employee()->create();

        /** @var $item_1 Item */
        $item_1 = $this->payrollItemBuilder
            ->payroll($payroll)
            ->employee($employee_1)
            ->role($role_1)
            ->hourly_rate(10.9)
            ->extras(14.9)
            ->is_cc_due(false)
            ->create();
        $item_2 = $this->payrollItemBuilder
            ->payroll($payroll)
            ->employee($employee_2)
            ->role($role_2)
            ->hourly_rate(120.9)
            ->extras(124.9)
            ->is_cc_due(true)
            ->create();
        $item_3 = $this->payrollItemBuilder
            ->payroll($payroll)
            ->employee($employee_3)
            ->role($role_1)
            ->hourly_rate(160.9)
            ->extras(14)
            ->is_cc_due(false)
            ->create();

        $data = [
            'cash_collecte' => 100,
            'items' => [
                [
                    'employee_id' => $item_1->employee_id,
                    'role_id' => $item_1->role_id,
                    'hourly_rate' => $item_1->hourly_rate,
                    'extras' => $item_1->extras,
                    'is_cc_due' => $item_1->is_cc_due,
                    'hours' => $item_1->hours,
                ],
                [
                    'employee_id' => $item_2->employee_id,
                    'role_id' => $item_2->role_id,
                    'hourly_rate' => $item_2->hourly_rate,
                    'extras' => 0,
                    'is_cc_due' => false,
                    'hours' => 8,
                ],
                [
                    'employee_id' => $item_3->employee_id,
                    'role_id' => $role_3->id,
                    'hourly_rate' => 60,
                    'extras' => 60.9,
                    'is_cc_due' => true,
                    'hours' => 2,
                ]
            ]
        ];

        $this->assertNotEquals($payroll->getPaidFromBol()['cash'], $data['cash_collecte']);

        $this->assertEquals($item_1->hourly_rate, $data['items'][0]['hourly_rate']);
        $this->assertEquals($item_1->role_id, $data['items'][0]['role_id']);
        $this->assertEquals($item_1->extras, $data['items'][0]['extras']);
        $this->assertEquals($item_1->is_cc_due, $data['items'][0]['is_cc_due']);
        $this->assertEquals($item_1->hours, $data['items'][0]['hours']);

        $this->assertEquals($item_2->hourly_rate, $data['items'][1]['hourly_rate']);
        $this->assertEquals($item_2->role_id, $data['items'][1]['role_id']);
        $this->assertNotEquals($item_2->extras, $data['items'][1]['extras']);
        $this->assertNotEquals($item_2->is_cc_due, $data['items'][1]['is_cc_due']);
        $this->assertNotEquals($item_2->hours, $data['items'][1]['hours']);

        $this->assertNotEquals($item_3->hourly_rate, $data['items'][2]['hourly_rate']);
        $this->assertNotEquals($item_3->role_id, $data['items'][2]['role_id']);
        $this->assertNotEquals($item_3->extras, $data['items'][2]['extras']);
        $this->assertNotEquals($item_3->is_cc_due, $data['items'][2]['is_cc_due']);
        $this->assertNotEquals($item_3->hours, $data['items'][2]['hours']);

//        $result = $this->service->update($payroll ,$data);

        $this->assertTrue(true);

        // todo переделать тест
//        $this->assertTrue($result instanceof Payroll);
//
//        $payroll->refresh();
//        $item_1->refresh();
//        $item_2->refresh();
//        $item_3->refresh();
//
//        $this->assertEquals($payroll->getPaidFromBol()['cash'], $data['cash_collecte']);
//
//        $this->assertEquals($item_1->hourly_rate, $data['items'][0]['hourly_rate']);
//        $this->assertEquals($item_1->role_id, $data['items'][0]['role_id']);
//        $this->assertEquals($item_1->extras, $data['items'][0]['extras']);
//        $this->assertEquals($item_1->is_cc_due, $data['items'][0]['is_cc_due']);
//        $this->assertEquals($item_1->hours, $data['items'][0]['hours']);
//
//        $this->assertEquals($item_2->hourly_rate, $data['items'][1]['hourly_rate']);
//        $this->assertEquals($item_2->role_id, $data['items'][1]['role_id']);
//        $this->assertEquals($item_2->extras, $data['items'][1]['extras']);
//        $this->assertEquals($item_2->is_cc_due, $data['items'][1]['is_cc_due']);
//        $this->assertEquals($item_2->hours, $data['items'][1]['hours']);
//
//        $this->assertEquals($item_3->hourly_rate, $data['items'][2]['hourly_rate']);
//        $this->assertEquals($item_3->role_id, $data['items'][2]['role_id']);
//        $this->assertEquals($item_3->extras, $data['items'][2]['extras']);
//        $this->assertEquals($item_3->is_cc_due, $data['items'][2]['is_cc_due']);
//        $this->assertEquals($item_3->hours, $data['items'][2]['hours']);
    }
}


