<?php

namespace Tests\Traits;

use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Models\Permissions\Role;

trait InteractsWithAuth
{
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
                    ->where(['name' => Role::superAdminName(), 'guard_name' => Admin::GUARD])
                    ->first()
            );
        }

        $this->actingAs($admin, Admin::GUARD);

        return $admin;
    }

    protected function loginAsEmployee(Employee $model = null): Employee
    {
        if (!$model) {
            $model = Employee::factory()->create();
        }

        $model->assignRole(
            Role::query()
                ->where(['name' => Role::employeeName(), 'guard_name' => Employee::GUARD])
                ->first()
        );
        $this->actingAs($model, Employee::GUARD);

        return $model;
    }
}
