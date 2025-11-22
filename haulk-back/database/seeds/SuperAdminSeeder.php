<?php

use App\Models\Admins\Admin;
use App\Models\Users\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{

    public function run(): void
    {
        if (App::environment('production')) {
            return;
        }

        $adminData = [
            'full_name' => 'saas super admin',
            'email' => 'admin@admin.com',
            'password' => '12345678',
            'status' => true,
        ];

        if (Admin::query()->where('email', $adminData['email'])->doesntExist()) {
            $admin = Admin::create($adminData);

            $admin->setPasswordAttribute($adminData['password']);

            $role = Role::query()->where(['name' => User::SUPERADMIN_ROLE, 'guard_name' => Admin::GUARD])->first();

            $admin->assignRole($role);
        }
    }
}
