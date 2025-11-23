<?php

namespace Tests\Builders\Users;

use App\Models\User\Role;
use Tests\Builders\BaseBuilder;

class RoleBuilder extends BaseBuilder
{

    function modelClass(): string
    {
        return Role::class;
    }

    public function id(int $value): self
    {
        $this->data['id'] = $value;
        return $this;
    }

    public function asPartner(): self
    {
        $this->data['title'] = Role::PARTNER;
        return $this;
    }

    public function asAdmin(): self
    {
        $this->data['title'] = Role::ADMIN;
        $this->data['id'] = 1;
        return $this;
    }

    public function asDriver(): self
    {
        $this->data['title'] = Role::DRIVERS;
        $this->data['id'] = 4;
        return $this;
    }

    public function asManager(): self
    {
        $this->data['title'] = Role::MANAGER;
        $this->data['id'] = 5;
        return $this;
    }
}
