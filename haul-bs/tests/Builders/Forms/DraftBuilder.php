<?php

namespace Tests\Builders\Forms;

use App\Models\Forms\Draft;
use App\Models\Users\User;
use Tests\Builders\BaseBuilder;

class DraftBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Draft::class;
    }

    public function path(string $value): self
    {
        $this->data['path'] = $value;
        return $this;
    }

    public function user(User $model): self
    {
        $this->data['user_id'] = $model;
        return $this;
    }

    public function body(array $value): self
    {
        $this->data['body'] = $value;
        return $this;
    }
}
