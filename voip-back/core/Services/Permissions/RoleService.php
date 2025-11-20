<?php

namespace Core\Services\Permissions;

use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Repositories\Permissions\RoleRepository;
use App\Services\AbstractService;

class RoleService extends AbstractService
{
    public function __construct()
    {
        $this->repo = resolve(RoleRepository::class);
        return parent::__construct();
    }

    public function assignDefaultRole(User $user): void
    {
        $user->assignRole(
            Role::query()
                ->forUsers()
                ->firstOrFail()
        );
    }
}
