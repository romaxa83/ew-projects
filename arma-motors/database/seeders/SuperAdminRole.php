<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use Exception;

class SuperAdminRole extends BaseSeeder
{
    public function run(): void
    {
        if(!Role::where(['name' => config('permission.roles.super_admin'), 'guard_name' => Admin::GUARD])->exists()){
            Role::create(
                [
                    'name' => config('permission.roles.super_admin'),
                    'guard_name' => Admin::GUARD,
                ]
            );
        }
    }

    /**
     * @throws Exception
     */
    public function clear(): void
    {
        Role::query()->where(
            [
                'name' => config('permission.roles.super_admin'),
                'guard_name' => Admin::GUARD,
            ]
        )
            ->delete();
    }
}
