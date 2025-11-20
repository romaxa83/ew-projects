<?php

namespace App\Repositories\User;

use App\Abstractions\AbstractRepository;
use App\Models\BaseModel;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return User::query();
    }

    public function getAllForAdmin(
        $relations = [],
        $filters = [],
        $order = [],
        $onlyActive = true
    )
    {
        // когда фронт перейдет на v2 роута - удалить проверку perPage
        $perPage = BaseModel::DEFAULT_PER_PAGE;
        if(isset($filters['per_page'])){
            $perPage = $filters['per_page'];
        }
        if(isset($filters['perPage'])){
            $perPage = $filters['perPage'];
        }

        $q = $this->query()
            ->notAdmin()
            ->with($relations)
            ->filter($filters)
        ;

        if($onlyActive){
            $q->where('status', true);
        }

        if(!empty($order)){
            foreach ($order as $field => $type) {
                $q->orderBy($field, $type);
            }
        }

        return $q->paginate($perPage);
    }

    public function getAdmins(): Collection
    {
        return $this->query()->whereHas('roles', function($q){
            $q->where('role', Role::ROLE_ADMIN);
        })->get();
    }
}
