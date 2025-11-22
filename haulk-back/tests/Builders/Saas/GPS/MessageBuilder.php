<?php

namespace Tests\Builders\Saas\GPS;

use App\Models\GPS\Message;
use App\Models\Saas\GPS\Device;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class MessageBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Message::class;
    }

    public function device(Device $model): self
    {
        $this->data['imei'] = $model->imei;
        return $this;
    }

    public function receivedAt(CarbonImmutable $value): self
    {
        $this->data['received_at'] = $value;
        return $this;
    }

    public function speed(int $value): self
    {
        $this->data['speed'] = $value;
        return $this;
    }

    public function engineOff(bool $value): self
    {
        $this->data['engine_off'] = $value;
        return $this;
    }
}


