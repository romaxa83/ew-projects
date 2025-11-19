<?php

declare(strict_types=1);

namespace Wezom\Core\Traits;

use Illuminate\Support\Facades\Hash;

trait SetPasswordTrait
{
    public function setPassword(string $password): self
    {
        $this->setAttribute('password', Hash::make($password));

        return $this;
    }
}
