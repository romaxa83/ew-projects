<?php

namespace App\Console\Commands\Stuff;

use App\Dto\Admins\AdminDto;
use App\Services\Admins\AdminService;
use Core\Services\Permissions\PermissionService;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateAdminCommand extends BaseCreateCommand
{
    protected $signature = 'admin:create';

    protected $description = 'Создание нового администратора.';

    /** @throws Throwable */
    public function handle(AdminService $service, PermissionService $permissionService): int
    {
        try {
            $args = $this->validated();
        } catch (ValidationException) {
            return self::FAILURE;
        }

        $admin = $service->create(AdminDto::byArgs($args));

        $admin->assignRole(
            $permissionService->firstOrCreateSuperAdminRole()
        );

        $this->info("Администратор создан.");

        return self::SUCCESS;
    }

    protected function emailUniqueRule(): string
    {
        return 'unique:admins,email';
    }
}
