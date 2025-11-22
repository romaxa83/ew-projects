<?php


namespace Database\Seeders;


use App\Enums\Permissions\UserRolesEnum;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\ValueObjects\Phone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    private const USERS = [
        [
            'first_name' => 'Pavel',
            'last_name' => 'Primal',
            'email' => 'primak.p.wezom@gmail.com',
            'language' => 'ru',
        ]
    ];

    public function run(): void
    {
        if (isProd() || isTesting()) {
            return;
        }

        foreach (self::USERS as $user) {
            if (User::whereEmail($user['email'])
                ->exists()) {
                continue;
            }

            $register = new User();
            $register->first_name = $user['first_name'];
            $register->last_name = $user['last_name'];
            $register->email = $user['email'];
            $register->password = Hash::make('password');
            $register->lang = $user['language'];

            $register->save();

            $register->syncRoles(
                Role::whereName(UserRolesEnum::INSPECTOR)
                    ->first()->id
            );
            $register->phones()
                ->create(
                    [
                        'phone' => new Phone('380' . mt_rand(100000000, 999999999)),
                        'is_default' => true
                    ]
                );
        }
    }
}
