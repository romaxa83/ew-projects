<?php

namespace WezomCms\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use WezomCms\Core\Models\Administrator;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'make:super-admin
                            {--name= : Administrator name}
                            {--email= : Administrator E-mail}
                            {--password= : Administrator password}';

    protected $description = 'Create super admin user';

    /**
     * @return bool|null
     */
    public function handle()
    {
        $this->info('Create super admin user');

        $name = $this->option('name') ?? $this->ask('Please specify administrator name', 'Admin');

        $email = $this->option('email') ?? $this->ask('Please specify administrator email');
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->error('Email is incorrect!');

            return false;
        }
        $presentUser = Administrator::where('email', $email)->exists();
        if ($presentUser) {
            $this->error('Email already exists in database.');

            return false;
        }

        $password = $this->option('password')
            ?? $this->secret('Please specify password for: ' . $email . '. Min length 8 characters');
        if (!$password) {
            $this->error('Password can`t be empty.');

            return false;
        }

        if (mb_strlen($password) < 8) {
            $this->error('The password should be at least 8 characters');

            return false;
        }

        $user = new Administrator();
        $user->name = $name;
        $user->email = $email;
        $user->super_admin = true;
        $user->active = true;
        $user->password = bcrypt($password);
        if (Schema::hasColumn($user->getTable(), 'phones')) {
            $user->phones = '[]';
        }

        if ($user->save()) {
            $this->info('User successfully created!');
        } else {
            $this->warn('Error creating a user');
        }
    }
}
