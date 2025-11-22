<?php


namespace Database\Seeders;


use App\Enums\Permissions\AdminRolesEnum;
use App\Models\Admins\Admin;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    private const SUPER_ADMIN = [
        'first_name' => 'Admin',
        'last_name' => 'Super',
        'second_name' => 'Test',
        'email' => 'super_admin@dalnoboy.com',
        'phone' => '380501234567',
        'password' => 'password',
    ];

    private const ADMIN = [
        'first_name' => 'Test',
        'last_name' => 'Admin',
        'second_name' => null,
        'email' => 'admin@dalnoboy.com',
        'phone' => '380507654321',
        'password' => 'password',
    ];

    public function run(): void
    {
        if (isProd() || isTesting()) {
            return;
        }
        $this->createAdmin(self::SUPER_ADMIN);
        $this->createAdmin(self::ADMIN, AdminRolesEnum::ADMIN);
    }

    private function createAdmin(array $adminData, string $role = AdminRolesEnum::SUPER_ADMIN): void
    {
        $admin = Admin::updateOrCreate(
            [
                'email' => new Email($adminData['email']),
            ],
            [
                'first_name' => $adminData['first_name'],
                'last_name' => $adminData['last_name'],
                'second_name' => $adminData['second_name'],
                'password' => Hash::make($adminData['password']),
                'lang' => Language::default()
                    ->first()->slug
            ]
        );

        $admin->assignRole(
            Role::whereName($role)
                ->first()
        );

        $admin->phones()
            ->updateOrCreate(
                [
                    'phone' => new Phone($adminData['phone']),
                ],
                [
                    'is_default' => true
                ]
            );
    }
}
