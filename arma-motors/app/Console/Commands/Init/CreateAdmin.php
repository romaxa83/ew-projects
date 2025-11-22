<?php

namespace App\Console\Commands\Init;

use App\DTO\Admin\AdminDTO;
use App\Repositories\Admin\AdminRepository;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    protected $signature = 'am:create-admin';

    protected $description = 'Create super admin';

    /**
     * @param AdminRepository $adminRepository
     */
    public function handle(AdminRepository $adminRepository)
    {
        $admin = [
            'name' => config('permission.roles.super_admin'),
            'email' => config('admin.super_admin.email'),
        ];

        $dto = AdminDTO::byArgs($admin);

        if($adminRepository->getByEmail($dto->getEmail())){
            $this->warn("super admin exist");
        } else {
            $admin = resolve(\App\Services\Admin\AdminService::class)->create($dto);
            $admin->assignRole(config('permission.roles.super_admin'));

            $this->info("super admin created");
        }

        $this->info("[✔] - email: {$dto->getEmail()}");
        $this->info("[✔] - password: {$dto->getPassword()}");
        $this->info("-------------------");
    }
}
