<?php

namespace Database\Factories\Ringostat;

use App\Models\Ringostat\EventAfterCall;
use Database\Factories\BaseFactory;

class EventAfterCallFactory extends BaseFactory
{
    protected $model = EventAfterCall::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'project_id' => '110070',
            'call_id' => 'us1_-1718037213.2974122',
            'type' => 'in',
            'scheme_name' => 'main',
            'status' => 'NO ANSWER',
            'destination' => '17732321869',
            'number_e164' => '+17082126499',
            'caller_number' => '17082126499',
            'employee' => 'Viktoria',
            'employee_estension' => '109',
            'employee_id' => '331841',
            'recording_presence' => 1,
            'recording' => 'https://app.ringostat.com/recordings/us1_-1718039451.2980672.ogg?token=bd23cf02e756a437b361df8bab85ed00',
            'recording_wav' => 'https://app.ringostat.com/recordings/us1_-1718039451.2980672.wav?token=bd23cf02e756a437b361df8bab85ed00',
            'duration_call' => '477',
            'duration_conversation' => '368',
            'duration_waiting' => '109',
            'call_date' => '2024-07-31 14:59:35',
            'call_timestamp' => 1722455975612863,
        ];
    }
}

