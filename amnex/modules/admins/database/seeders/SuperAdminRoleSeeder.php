<?php

namespace Wezom\Admins\Database\Seeders;

use Illuminate\Database\Seeder;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Models\Permission\Role;

class SuperAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate([
            'system_type' => RoleEnum::SUPER_ADMIN,
            'guard_name' => Admin::GUARD,
        ], [
            'name' => 'Super Admin',
        ]);
    }
}
