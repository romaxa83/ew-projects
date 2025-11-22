<?php

namespace App\Traits\Models;

use Illuminate\Support\Facades\Hash;

trait SetPassword
{
    public function setPassword(string $password, bool $save = false): self
    {
        $this->setAttribute('password', Hash::make($password));
        $this->setAttribute('password_verified_code', null);

        if($save){
            $this->save();
        }

        return $this;
    }
}

