<?php

namespace App\Console\Commands\Stuff;

use App\Dto\Moderators\ModeratorDto;
use App\Services\Moderators\ModeratorService;
use Core\Services\Permissions\PermissionService;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateModeratorCommand extends BaseCreateCommand
{
    protected $signature = 'moderator:create';

    protected $description = 'Создание нового модератора системы 1с.';

    /** @throws Throwable */
    public function handle(ModeratorService $service, PermissionService $permissionService): int
    {
        try {
            $args = $this->validated();
        } catch (ValidationException) {
            return self::FAILURE;
        }

        $moderator = $service->create(ModeratorDto::byArgs($args));

        $moderator->assignRole(
            $permissionService->firstOrCreateModeratorRole()
        );

        $this->info("Модератор создан.");

        return self::SUCCESS;
    }

    protected function emailUniqueRule(): string
    {
        return 'unique:moderators,email';
    }
}
