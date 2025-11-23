<?php

namespace Database\Factories\Twilio;

use App\Models\Division;
use App\Models\Twilio\TwilioSms;
use Database\Factories\BaseFactory;

class TwilioFactory extends BaseFactory
{
    protected $model = TwilioSms::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'division' => Division::factory(),
            'sid' => 'SM42fc2330c1b740e0b3830d7476d88d4f',
            'direction' => 'outbound-api',
            'from' => '+12137847797',
            'to' => '+12014222730',
            'body' => 'some txt',
            'misc' => [],
        ];
    }
}
