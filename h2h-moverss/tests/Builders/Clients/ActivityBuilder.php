<?php

namespace Tests\Builders\Clients;

use App\Models\Client;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class ActivityBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Client\Activity::class;
    }

    public function client(Client $model): self
    {
        $this->data['client_id'] = $model->id;
        return $this;
    }

    public function type(string $value): self
    {
        $this->data['type'] = $value;
        return $this;
    }

    public function miscs(array $value): self
    {
        $this->data['miscs'] = $value;
        return $this;
    }

    public function created_at(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }
}
