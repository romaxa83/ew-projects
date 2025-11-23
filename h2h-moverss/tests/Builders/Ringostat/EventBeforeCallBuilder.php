<?php

namespace Tests\Builders\Ringostat;

use App\Models\Client;
use App\Models\Ringostat\EventBeforeCall;
use Tests\Builders\BaseBuilder;

class EventBeforeCallBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return EventBeforeCall::class;
    }

    public function projectId(string $value): self
    {
        $this->data['project_id'] = $value;
        return $this;
    }

    public function destination(string $value): self
    {
        $this->data['destination'] = $value;
        return $this;
    }

    public function call_id(string $value): self
    {
        $this->data['call_id'] = $value;
        return $this;
    }

    public function call_type(string $value): self
    {
        $this->data['call_type'] = $value;
        return $this;
    }

    public function client(Client $model): self
    {
        $this->data['client_id'] = $model->id;
        return $this;
    }
}
