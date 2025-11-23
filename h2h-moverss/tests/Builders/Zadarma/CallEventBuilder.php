<?php

namespace Tests\Builders\Zadarma;

use App\Models\Client;
use App\Models\Zadarma\CallsEvents;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class CallEventBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return CallsEvents::class;
    }

    public function pbx_call_id(string $value): self
    {
        $this->data['pbx_call_id'] = $value;
        return $this;
    }

    public function pbx_id(int $value): self
    {
        $this->data['pbx_id'] = $value;
        return $this;
    }

    public function caller_id(?string $value): self
    {
        $this->data['caller_id'] = $value;
        return $this;
    }

    public function destination(string $value): self
    {
        $this->data['destination'] = $value;
        return $this;
    }

    public function disposition(string $value): self
    {
        $this->data['disposition'] = $value;
        return $this;
    }

    public function event(string $value): self
    {
        $this->data['event'] = $value;
        return $this;
    }

    public function status_code(int $value): self
    {
        $this->data['status_code'] = $value;
        return $this;
    }

    public function internal(?int $value): self
    {
        $this->data['internal'] = $value;
        return $this;
    }

    public function call_start_at(CarbonImmutable $value): self
    {
        $this->data['call_start'] = $value;
        return $this;
    }

    public function client(Client $model): self
    {
        $this->data['client_id'] = $model->id;
        return $this;
    }
}
