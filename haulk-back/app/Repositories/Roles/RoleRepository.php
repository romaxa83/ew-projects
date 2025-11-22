<?php

namespace App\Repositories\Roles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    /**
     * @param int $id
     * @return Role|Model|null
     */
    public function findById(int $id): ?Role
    {
        return $this->query()->find($id);
    }

    public function query(): Builder
    {
        return Role::query();
    }

    /**
     * @param string $name
     * @return Role|Model|null
     */
    public function findByName(string $name): ?Role
    {
        static $roles;

        if (!isset($roles[$name])) {
            $roles[$name] = $this->query()->where('name', $name)->first();
        }

        return $roles[$name];
    }

    public function allToArray(): array
    {
        return $this->query()->pluck('name', 'id')->toArray();
    }
}
