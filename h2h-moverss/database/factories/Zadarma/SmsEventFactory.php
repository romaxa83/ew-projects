<?php

namespace Database\Factories\Zadarma;

use App\Models\Zadarma\SmsEvents;
use Database\Factories\BaseFactory;

class SmsEventFactory extends BaseFactory
{
    protected $model = SmsEvents::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'pbx_id' => '339617',
            'inbound' => 1,
            'caller_id' => '12137861305',
            'caller_did' => '16572546313',
            'text' => 'some txt',
        ];
    }
}
