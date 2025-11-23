<?php

namespace Tests\Builders;

use WezomCms\Users\Models\User;

class UserBuilder
{
    private array $data = [
        'active' => true,
    ];
    private array $wishlist = [];

    public function setName($value): self
    {
        $this->data['name'] = $value;

        return $this;
    }

    public function setSurname($value): self
    {
        $this->data['surname'] = $value;

        return $this;
    }

    public function setWishlist($value): self
    {
        $this->wishlist[] = $value;

        return $this;
    }

    public function setEmail($value): self
    {
        $this->data['email'] = $value;

        return $this;
    }

    public function setPhone($value): self
    {
        $this->data['phone'] = $value;

        return $this;
    }

    public function setActive(bool $value): self
    {
        $this->data['active'] = $value;

        return $this;
    }

    public function create(): User
    {
        $user = $this->save();

        if (!empty($this->wishlist)) {
            $user->wishlist()->attach($this->wishlist);
        }

        return $user;
    }

    private function save(): User
    {
        return User::factory()->new($this->data)->create();
    }
}
