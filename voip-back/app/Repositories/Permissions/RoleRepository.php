<?php

namespace App\Repositories\Permissions;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;

final class RoleRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Role::class;
    }

    public function getCustomList(
        array $selectFields = [],
        array $filter = [],
        array $relations = [],
        string $guard = Admin::GUARD,
    ): Collection
    {
        return Role::query()
            ->select($selectFields)
            ->latest()
            ->filter($filter)
            ->withoutSuperAdmin()
            ->with($relations)
            ->where('guard_name', $guard)
            ->get();
    }

    public function getEmployeeRole(): Role
    {
        return Role::query()
            ->where('name', Role::employeeName())
            ->first();
    }
}
