<?php

namespace Tests\Builders\Clients;

use App\Models\Client;
use Tests\Builders\BaseBuilder;

class EmailBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Client\Email::class;
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

    public function is_primary(int $value): self
    {
        $this->data['is_primary'] = $value;
        return $this;
    }
}
