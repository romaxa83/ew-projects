<?php

namespace Database\Factories\Zadarma;

use App\Models\Zadarma\CallsEvents;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;

class CallEventFactory extends BaseFactory
{
    protected $model = CallsEvents::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'event' => 'NOTIFY_END',
            'pbx_id' => '339617',
            'call_start' => CarbonImmutable::now()->subDay(),
            'pbx_call_id' => 'in_0892e63cb09c4d7a0550f77314fcd216156021ca',
            'caller_id' => '380931775529',
            'destination' => '380676597909',
            'called_did' => null,
            'internal' => '100',
            'duration' => '14',
            'disposition' => 'answered',
            'status_code' => '16',
            'is_recorded' => 1,
            'call_id_with_rec' => '1647439288.1115107',
            'client_id' => null,
        ];
    }
}
