<?php

namespace App\Services\Auth;

use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Models\Logins\Login;

class LoginService
{
    public function create(Admin|Employee $model): void
    {
        $login = new Login();
        $login->model_type = $model::MORPH_NAME;
        $login->model_id = $model->id;

        $login->save();
    }
}
