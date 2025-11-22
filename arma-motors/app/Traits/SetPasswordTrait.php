<?php

namespace App\Traits;

use Hash;

trait SetPasswordTrait
{
    public function setPassword(string $password): self
    {
        $this->setAttribute('password', Hash::make($password));

        return $this;
    }
}
