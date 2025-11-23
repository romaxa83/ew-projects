<?php

namespace Database\Factories\Orders\Payroll;

use App\Models\Employee;
use App\Models\Order;
use App\Models\Order\Payroll\Payroll;
use App\Models\User\Role;
use Database\Factories\BaseFactory;

class ItemFactory extends BaseFactory
{
    protected $model = Order\Payroll\Item::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'payroll_id' => Payroll::factory(),
            'employee_id' => Employee::factory(),
            'role_id' => Role::factory(),
            'hourly_rate' => 44,
            'hours' => 4,
            'extras' => 44,
            'is_cc_due' => false,
        ];
    }
}