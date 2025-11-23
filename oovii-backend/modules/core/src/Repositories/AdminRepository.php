<?php

namespace WezomCms\Core\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Core\Models\Administrator;

class AdminRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Administrator::query();
    }

    public function getSuperAdmin($selectField = ['*'] , $relations = [])
    {
        return Administrator::query()
            ->with($relations)
            ->select($selectField)
            ->where('super_admin', true)
            ->first();
    }

    public function getByRoleForSelect($role, $relations = ['roles'], $choiceText = false): array
    {
        $models = $this->query()
            ->with($relations)
            ->whereHas('roles', function ($q) use ($role){
                return $q->where('name', $role);
            })
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        if ($choiceText) {
            $choice[null] = $choiceText;
            $models = $choice + $models;
        }

        return $models;
    }

    public function getByRoleAndId($id, $role, array $relations = ["roles"]): ?Administrator
    {
        return $this->query()
            ->with($relations)
            ->whereHas("roles", function ($q) use ($role) {
                return $q->where("name", $role);
            })
            ->where('id', $id)
            ->first();
    }

    public function existByRoleAndId($id, $role, array $relations = ["roles"]): bool
    {
        return $this->query()
            ->with($relations)
            ->whereHas("roles", function ($q) use ($role) {
                return $q->where("name", $role);
            })
            ->where('id', $id)
            ->exists();
    }
}
