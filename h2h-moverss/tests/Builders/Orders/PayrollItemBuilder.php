<?php

namespace Tests\Builders\Orders;

use App\Models\Employee;
use App\Models\Order;
use App\Models\User\Role;
use Tests\Builders\BaseBuilder;

class PayrollItemBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Payroll\Item::class;
    }

    public function payroll(Order\Payroll\Payroll $model): self
    {
        $this->data['payroll_id'] = $model->id;
        return $this;
    }

    public function employee(Employee $model): self
    {
        $this->data['employee_id'] = $model->id;
        return $this;
    }

    public function role(Role $model): self
    {
        $this->data['role_id'] = $model->id;
        return $this;
    }

    public function hourly_rate(float $value): self
    {
        $this->data['hourly_rate'] = $value;
        return $this;
    }

    public function extras(float $value): self
    {
        $this->data['extras'] = $value;
        return $this;
    }

    public function is_cc_due(bool $value): self
    {
        $this->data['is_cc_due'] = $value;
        return $this;
    }
}
