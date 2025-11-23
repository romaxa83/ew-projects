<?php

namespace Database\Factories\Communications;

use App\Enums\Communications\Type;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Twilio\TwilioSms;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;

class CommunicationRecordFactory extends BaseFactory
{
    protected $model = CommunicationRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $model = TwilioSms::factory()->create();

        return [
            'entity_type' => TwilioSms::MORPH_NAME,
            'entity_id' => $model->id,
            'client_id' => null,
            'order_id' => null,
            'client_ids' => [],
            'division_id' => Division::factory(),
            'type' => Type::Inner(),
            'is_answered' => true,
            'channel_contact' => null,
            'sort_at' => CarbonImmutable::now(),
        ];
    }
}
