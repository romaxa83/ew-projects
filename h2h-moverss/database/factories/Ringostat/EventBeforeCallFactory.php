<?php

namespace Database\Factories\Ringostat;

use App\Models\Ringostat\EventBeforeCall;
use Database\Factories\BaseFactory;

class EventBeforeCallFactory extends BaseFactory
{
    protected $model = EventBeforeCall::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'project_id' => '110070',
            'call_date_microsecond' => '1722455975612863',
            'call_type' => 'out',
            'destination' => '17732321869',
            'number_e164' => '+17082126499',
            'callers_number' => '17082126499',
            'employee_ringostat_id' => null,
            'client_id' => null,
            'call_date' => null,
            'call_id' => 'us1_-1731068394.1647050',
            'responsible_employees' => null,
            'from_event' => null,
        ];
    }
}

