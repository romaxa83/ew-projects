<?php

namespace App\Rules;

use App\Repositories\Employees\EmployeeRepository;
use Illuminate\Support\Facades\Hash;

class LoginEmployee extends LoginAdmin
{
    public function passes($attribute, $value): bool
    {
        /** @var $repo EmployeeRepository */
        $repo = resolve(EmployeeRepository::class);
        if (!$model = $repo->getBy('email', $this->args['username'])) {
            return false;
        }

        return Hash::check($this->args['password'], $model->password);
    }
}
