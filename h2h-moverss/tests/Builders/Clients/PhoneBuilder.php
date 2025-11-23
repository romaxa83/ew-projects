<?php

namespace Tests\Builders\Clients;

use App\Models\Client;
use Tests\Builders\BaseBuilder;

class PhoneBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Client\Phone::class;
    }

    public function client(Client $model): self
    {
        $this->data['client_id'] = $model->id;
        return $this;
    }

    public function value(string $value): self
    {
        $this->data['value'] = $value;
        return $this;
    }
}
