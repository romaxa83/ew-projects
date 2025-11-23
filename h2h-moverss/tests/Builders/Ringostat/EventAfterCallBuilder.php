<?php

namespace Tests\Builders\Ringostat;

use App\Models\Ringostat\EventAfterCall;
use Tests\Builders\BaseBuilder;

class EventAfterCallBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return EventAfterCall::class;
    }

    public function projectId(string $value): self
    {
        $this->data['project_id'] = $value;
        return $this;
    }

    public function callerNumber(string $value): self
    {
        $this->data['caller_number'] = $value;
        return $this;
    }

    public function destination(string $value): self
    {
        $this->data['destination'] = $value;
        return $this;
    }

    public function type(string $value): self
    {
        $this->data['type'] = $value;
        return $this;
    }

    public function status(string $value): self
    {
        $this->data['status'] = $value;
        return $this;
    }
}
