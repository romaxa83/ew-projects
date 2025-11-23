<?php

namespace Tests\Builders\Twilio;

use App\Models\Division;
use App\Models\Twilio\TwilioSms;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class TwilioSmsBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return TwilioSms::class;
    }

    public function division(Division $model): self
    {
        $this->data['division'] = $model->id;
        return $this;
    }

    public function misc(array $value): self
    {
        $this->data['misc'] = $value;
        return $this;
    }

    public function from(string $value): self
    {
        $this->data['from'] = $value;
        return $this;
    }

    public function to(string $value): self
    {
        $this->data['to'] = $value;
        return $this;
    }

    public function direction(string $value): self
    {
        $this->data['direction'] = $value;
        return $this;
    }

    public function created_at(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }
}
