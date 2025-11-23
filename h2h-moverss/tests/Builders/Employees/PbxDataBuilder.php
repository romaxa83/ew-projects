<?php

namespace Tests\Builders\Employees;

use App\Models\Employee;
use App\Models\Zadarma\CallsEvents;
use Tests\Builders\BaseBuilder;

class PbxDataBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Employee\PbxData::class;
    }

    public function employee(Employee $model): self
    {
        $this->data['employee_id'] = $model->id;
        return $this;
    }

    public function pbx_id(int $value): self
    {
        $this->data['pbx_id'] = $value;
        return $this;
    }

    public function pbx_ext(?int $value): self
    {
        $this->data['pbx_ext'] = $value;
        return $this;
    }

    public function sip_status(bool $value): self
    {
        $this->data['sip_status'] = $value;
        return $this;
    }

    public function call_rec_id(CallsEvents $model): self
    {
        $this->data['call_rec_id'] = $model;
        return $this;
    }
}
