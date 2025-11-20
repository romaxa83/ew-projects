<?php

namespace App\Console\Commands\Init;

use App\Console\Commands\Admins\CreateAdminCommand;
use App\Repositories\Admins\AdminRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitProject extends Command
{
    protected $signature = 'voip:init';

    protected $description = 'Инициализация проекта';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->createSuperAdmin();
    }

    protected function createSuperAdmin()
    {
        /** @var $repo AdminRepository */
        $repo = resolve(AdminRepository::class);

        if(!$repo->getSuperAdmin()){
            Artisan::call(CreateAdminCommand::class, [
                '--n' => 'super admin',
                '--e' => 'admin@gmail.com',
                '--p' => 'password1',
            ]);
        }

        return self::SUCCESS;
    }
}

