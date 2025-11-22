<?php

namespace App\Events\Events\Users;

use App\Models\Users\User;

class UserChangedEvent
{
    public function __construct(
        protected User $model,
        protected array $additional = [],
    )
    {}

    public function getModel(): User
    {
        return $this->model;
    }

    public function getAdditional(): array
    {
        return $this->additional;
    }
}
