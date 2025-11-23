<?php

namespace Database\Factories\Employees;

use App\Models\Employee;
use Database\Factories\BaseFactory;

class PbxDataFactory extends BaseFactory
{
    protected $model = Employee\PbxData::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'pbx_id' => 339617,
            'pbx_ext' => null,
            'pbx_password' => null,
            'pbx_show_webrtc' => 0,
            'sip_status' => 0,
            'call_rec_id' => null,
        ];
    }
}
