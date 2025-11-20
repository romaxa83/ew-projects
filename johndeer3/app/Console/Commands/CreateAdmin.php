<?php

namespace App\Console\Commands;

use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\User\RoleRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Class CreateAdmin
 *
 * @package App\Console\Commands
 */
class CreateAdmin extends Command
{
    protected $signature = 'jd:create:admin';

    protected $description = 'Create admin';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $role = resolve(RoleRepository::class)->getRole(Role::ROLE_ADMIN);

        $login = $this->ask('Enter Login');
        $password = $this->ask('Enter Password');
        $email = $this->ask('Enter Email');

        if ($this->confirm("Create admin - login [{$login}], password [{$password}], email [{$email}]", true)) {

            if($login === null || $password === null || $email === null){
                $this->warn("Значения не могут быть пустыми");
                return;
            }

            $admin = User::query()->where('login', $login)->where('email', $email)->exists();
            if($admin){
                $this->warn("Есть админ с переданными данными");
                return;
            }

            $admin = new User();
            $admin->login = $login;
            $admin->password = Hash::make($password);
            $admin->email = $email;
            $admin->save();

            $admin->roles()->attach($role);

            $this->info('Admin created.');
        }
    }
}
