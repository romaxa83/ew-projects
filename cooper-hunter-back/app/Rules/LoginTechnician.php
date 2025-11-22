<?php

namespace App\Rules;

use App\Models\Technicians\Technician;
use Illuminate\Support\Facades\Hash;

class LoginTechnician extends LoginAdmin
{
    public function passes($attribute, $value): bool
    {
        if (!$technician = Technician::query()->where('email', $this->args['username'])->first()) {
            return false;
        }

        return Hash::check($this->args['password'], $technician->password);
    }
}
