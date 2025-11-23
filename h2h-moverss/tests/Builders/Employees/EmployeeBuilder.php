<?php

namespace Tests\Builders\Employees;

use App\Enums\Employee\SalesTeamEnum;
use App\Models\Employee;
use App\User;
use Tests\Builders\BaseBuilder;

class EmployeeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Employee::class;
    }

    public function user(User $model): self
    {
        $this->data['auth_user_id'] = $model->id;
        return $this;
    }

    public function active(int $value): self
    {
        $this->data['active'] = $value;
        return $this;
    }

    public function sales_team(?SalesTeamEnum $value): self
    {
        $this->data['sales_team'] = $value;
        return $this;
    }

    public function pbx_ext(?int $value): self
    {
        $this->data['pbx_ext'] = $value;
        return $this;
    }

    public function zadarma_sip_status(bool $value): self
    {
        $this->data['zadarma_sip_status'] = $value;
        return $this;
    }

    public function ringostat_id(int $value): self
    {
        $this->data['ringostat_id'] = $value;
        return $this;
    }

    public function ringostat_sip_status(bool $value): self
    {
        $this->data['ringostat_sip_status'] = $value;
        return $this;
    }

    public function ringostat_call_rec_id(int $value): self
    {
        $this->data['ringostat_call_rec_id'] = $value;
        return $this;
    }

    public function callers_number(string $value): self
    {
        $this->data['callers_number'] = $value;
        return $this;
    }

    public function zadarma_call_rec_id(int $value): self
    {
        $this->data['zadarma_call_rec_id'] = $value;
        return $this;
    }

    public function ringostat_miscs(array $value): self
    {
        $this->data['ringostat_miscs'] = $value;
        return $this;
    }
}

