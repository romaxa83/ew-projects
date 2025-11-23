<?php

namespace Tests\Builders\Zadarma;

use App\Models\Zadarma\SmsEvents;
use Tests\Builders\BaseBuilder;

class SmsEventBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return SmsEvents::class;
    }

    public function caller_id(string $value): self
    {
        $this->data['caller_id'] = $value;
        return $this;
    }

    public function caller_did(string $value): self
    {
        $this->data['caller_did'] = $value;
        return $this;
    }

    public function inbound(int $value): self
    {
        $this->data['inbound'] = $value;
        return $this;
    }
}
