<?php

namespace App\Services;

use App\Models\Admins\Admin;
use App\Models\BaseAuthenticatable;
use App\Models\BaseModel;
use App\Models\Employees\Employee;
use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractService
{
    public RepositoryInterface|null $repo = null;

    public function __construct()
    {}

    public function toggleActive(Model|int $model): Model
    {
        $model = $this->getModelIfArgsAsID($model);

        $model->active = !$model->active;
        $model->save();

        return $model;
    }

    public function delete(BaseModel|BaseAuthenticatable|int $model): bool
    {
        $model = $this->getModelIfArgsAsID($model);

        return $model->delete();
    }

    public function forceDelete(BaseModel|BaseAuthenticatable|int $model): bool
    {
        $model = $this->getModelIfArgsAsID($model);

        return $model->forceDelete();
    }

    protected function getModelIfArgsAsID(Model|int $model): Model
    {

        if(is_numeric($model)){
            if(!$this->repo){
                throw new ServiceException("repository not connected");
            }

            return $this->repo->getBy('id', $model);
        }

        return $model;
    }

    public function changePassword(Authenticatable|Admin|Employee $user, string $password): bool
    {
        return $user->setPassword($password)->save();
    }
}
