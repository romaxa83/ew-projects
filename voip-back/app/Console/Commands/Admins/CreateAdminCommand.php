<?php

namespace App\Console\Commands\Admins;

use App\Dto\Admins\AdminDto;
use App\Services\Admins\AdminService;
use Core\Services\Permissions\PermissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Throwable;
class CreateAdminCommand extends Command
{
    public const QUESTION_NAME = 'Name: ';
    public const QUESTION_EMAIL = 'Email: ';
    public const QUESTION_PASSWORD = 'Password: ';

    protected $signature = 'admin:create {--n=} {--e=} {--p=}';

    protected $description = 'Создание нового администратора.';

    /**
     * @throws Throwable
     */
    public function handle(AdminService $service, PermissionService $permissionService): void
    {
        $args = [
            'name' => $this->option('n') ?? $this->ask(self::QUESTION_NAME),
            'email' => $this->option('e') ?? $this->ask(self::QUESTION_EMAIL),
            'password' => $this->option('p') ?? $this->ask(self::QUESTION_PASSWORD),
        ];

        $this->validation($args);

        $admin = $service->create(AdminDto::byArgs($args));

        $admin->assignRole(
            $permissionService->firstOrCreateSuperAdminRole()
        );

        $this->info("Администратор создан.");
    }

    protected function validation(array $args): void
    {
        Validator::validate(
            $args,
            [
                'name' => ['required', 'string', 'min:3'],
                'email' => ['required', 'email', 'unique:admins,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );
    }
}
