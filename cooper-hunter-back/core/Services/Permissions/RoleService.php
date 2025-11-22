<?php

namespace Core\Services\Permissions;

use App\Models\Dealers\Dealer;
use App\Models\Permissions\Role;
use App\Models\Technicians\Technician;
use App\Models\Users\User;

class RoleService
{
    public function assignDefaultRole(User $user): void
    {
        $user->assignRole(
            Role::query()
                ->forUsers()
                ->whereName(config('permission.roles.user'))
                ->firstOrFail()
        );
    }

    public function assignTechnicianDefaultRole(Technician $technician): void
    {
        $technician->assignRole(
            Role::query()
                ->forTechnicians()
                ->whereName(config('permission.roles.technician'))
                ->firstOrFail()
        );
    }

    public function assignDealerDefaultRole(Dealer $dealer): void
    {
        if(!$dealer->role) {
            $dealer->assignRole(
                Role::query()
                    ->forDealers()
                    ->whereName(config('permission.roles.dealer'))
                    ->firstOrFail()
            );
        }
    }
}
