<?php

namespace Tests\Builders\Calls;

use App\Models\Calls\IncomingCall;
use App\Models\Client;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class IncomingCallBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return IncomingCall::class;
    }

    public function client(Client $model): self
    {
        $this->data['client_id'] = $model->id;
        return $this;
    }

    public function call_id(string $value): self
    {
        $this->data['call_id'] = $value;
        return $this;
    }

    public function created_at(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }
}
