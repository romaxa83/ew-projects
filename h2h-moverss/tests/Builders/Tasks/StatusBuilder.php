<?php

namespace Tests\Builders\Tasks;

use App\Models\Tasks;
use Tests\Builders\BaseBuilder;

class StatusBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Tasks\Status::class;
    }

    public function id(int $value): self
    {
        $this->data['id'] = $value;
        return $this;
    }
}
