<?php

namespace App\Services\Roles;

use App\Models\Users\User;
use App\Repositories\Roles\RoleRepository;
use Spatie\Permission\Models\Role;

class RoleService
{
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function findById(int $id): ?Role
    {
        return $this->roleRepository->findById($id);
    }

    public function findByName(string $name): ?Role
    {
        return $this->roleRepository->findByName($name);
    }

    public function permittedForUser(User $user): iterable
    {
        $result = collect();

        $this->roleRepository->query()->each(
            function (Role $role) use ($user, $result) {
                $roleName = $role->getAttribute('name');
                if ($user->can('roles ' . strtolower($roleName))) {
                    // remove superadmin permissions for superadmin
                    if ($roleName == User::SUPERADMIN_ROLE) {
                        return;
                    }
                    // remove superadmin permissions for superadmin

                    $result->push($role);
                }
            }
        );

        return $result;
    }
}
