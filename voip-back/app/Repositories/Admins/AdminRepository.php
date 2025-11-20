<?php

namespace App\Repositories\Admins;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class AdminRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Admin::class;
    }
    public function hasAdminWithRole(Role $role): bool
    {
        return Admin::query()
            ->with('roles')
            ->whereHas('roles', fn ($q) => $q->where('name', $role->name))
            ->exists();
    }

    public function getByRole(Role $role): Collection
    {
        return Admin::query()
            ->with('roles')
            ->whereHas('roles', fn ($q) => $q->where('name', $role->name))
            ->get();
    }

    public function getSuperAdmin(): ?Admin
    {
        return Admin::query()
            ->with('roles')
            ->whereHas('roles', fn ($q) => $q->where('name', config('permission.roles.super_admin')))
            ->first();
    }

    public function getPaginatorFromRole(
        Admin $admin,
        array $select = ['*'],
        array $relations = [],
        array $filters = [],
    ): LengthAwarePaginator
    {
        return Admin::query()
            ->select($select)
            ->with($relations)
            ->latest()
            ->filter($filters)
            ->when(!$admin->isSuperAdmin(), fn($q) => $q->where('id', $admin->id))
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            )
            ;
    }

    public function getListFromRole(
        Admin $admin,
        array $select = ['*'],
        array $relations = [],
        array $filters = [],
    ): \Illuminate\Support\Collection
    {
        return Admin::query()
            ->select($select)
            ->with($relations)
            ->latest()
            ->filter($filters)
            ->active()
            ->when(!$admin->isSuperAdmin(), fn($q) => $q->where('id', $admin->id))
            ->when($admin->isSuperAdmin(), fn($q) => $q->withoutAdmin($admin->id))
            ->orderBy('name')
            ->get()
            ;
    }
}
