<?php

namespace Tests\Traits;

use App\Models\Admins\Admin;
use App\Models\Dealers\Dealer;
use App\Models\OneC\Moderator;
use App\Models\Permissions\Role;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Tests\Builders\Dealers\DealerBuilder;

trait InteractsWithAuth
{
    protected function loginAsUser(User $user = null): User
    {
        if (!$user) {
            $user = User::factory()->create();
        }

        $this->actingAs($user, User::GUARD);

        return $user;
    }

    protected function loginAsTechnician(Technician $technician = null): Technician
    {
        if (!$technician) {
            $technician = Technician::factory()->certified()->create();
        }

        $this->actingAs($technician, Technician::GUARD);

        return $technician;
    }

    protected function loginAsTechnicianWithRole(Technician $technician = null, Role $role = null): Technician
    {
        if (!$technician) {
            $technician = Technician::factory()
                ->certified()
                ->verified()
                ->create();
        }

        if (!$role) {
            $role = Role::query()
                ->where(['name' => config('permission.roles.technician'), 'guard_name' => Technician::GUARD])
                ->first();
        }

        $technician->assignRole($role);

        $this->actingAs($technician, Technician::GUARD);

        return $technician;
    }

    protected function loginAsUserWithRole(User $user = null, Role $role = null): User
    {
        if (!$user) {
            $user = User::factory()->create();
        }

        if (!$role) {
            $role = Role::query()
                ->where(['name' => config('permission.roles.user'), 'guard_name' => User::GUARD])
                ->first();
        }

        $user->assignRole($role);

        $this->actingAs($user, User::GUARD);

        return $user;
    }

    protected function loginAsAdmin(Admin $admin = null): Admin
    {
        if (!$admin) {
            $admin = Admin::factory()->create();
        }

        $this->actingAs($admin, Admin::GUARD);

        return $admin;
    }

    protected function loginAsSuperAdmin(Admin $admin = null): Admin
    {
        if (!$admin) {
            $admin = Admin::factory()->create();
        }

        if (!$admin->isSuperAdmin()) {
            $admin->assignRole(
                Role::query()
                    ->where(['name' => config('permission.roles.super_admin'), 'guard_name' => Admin::GUARD])
                    ->first()
            );
        }

        $this->actingAs($admin, Admin::GUARD);

        return $admin;
    }

    protected function loginAsModerator(Moderator $moderator = null, Role $role = null): Moderator
    {
        if (!$moderator) {
            $moderator = Moderator::factory()->create();
        }

        if (!$role) {
            $role = Role::query()
                ->where(['name' => config('permission.roles.1c_moderator'), 'guard_name' => Moderator::GUARD])
                ->first();
        }

        $moderator->assignRole($role);

        $this->actingAs($moderator, Moderator::GUARD);

        return $moderator;
    }

    protected function loginAsDealer(Dealer $model = null): Dealer
    {
        if (!$model) {
            $model = resolve(DealerBuilder::class)->create();
        }

        $this->actingAs($model, Dealer::GUARD);

        return $model;
    }

    protected function loginAsDealerWithRole(Dealer $model = null, Role $role = null): Dealer
    {
        if (!$model) {
            $model = resolve(DealerBuilder::class)->create();
        }

        if (!$role) {
            $role = Role::query()
                ->where(['name' => config('permission.roles.dealer'), 'guard_name' => Dealer::GUARD])
                ->first();
        }

        $model->assignRole($role);

        $this->actingAs($model, Dealer::GUARD);

        return $model;
    }
}
