<?php

namespace App\Repositories\User;

use App\Abstractions\AbstractRepository;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Builder;

class RoleRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Role::query();
    }

    public function getRoles(): array
    {
        return $this->query()
            ->with(['current'])
            ->where('role', '!=', Role::ROLE_ADMIN)
            ->get()
            ->pluck('current.0.text', 'role')
            ->toArray()
            ;
    }
}
