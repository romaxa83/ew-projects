<?php

namespace Database\Factories\Calls;

use App\Enums\ProviderEnum;
use App\Models\Calls\IncomingCall;
use Database\Factories\BaseFactory;

class IncomingCallFactory extends BaseFactory
{
    protected $model = IncomingCall::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'provider' => ProviderEnum::Ringostat(),
            'call_id' => '3423rfesr234',
            'phone' => '324234324',
            'client_id' => null,
        ];
    }
}
