<?php

namespace App\Traits\Model;

use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Models\Logins\Login;
use App\Repositories\Admins\AdminRepository;
use App\Repositories\Employees\EmployeeRepository;
use App\Services\Auth\LoginService;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property-read Collection|Login[] $logins
 */
trait LoginDataTrait
{
    public function logins()
    {
        return $this->morphMany(Login::class, 'model');
    }

    public function createLoginRecordByEmail(string $email, string $guard): null|Employee|Admin
    {
        $model =null;
        if($guard === Admin::GUARD){
            $repo = resolve(AdminRepository::class);
            $model = $repo->getBy('email', $email);
        }
        if($guard === Employee::GUARD){
            $repo = resolve(EmployeeRepository::class);
            $model = $repo->getBy('email', $email);
        }

        if($model){
            /** @var $service LoginService */
            /** @var $model Admin|Employee */
            $service = resolve(LoginService::class);
            $service->create($model);
        }

        return $model;
    }
}


