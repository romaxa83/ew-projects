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

    protected $signature = 'admin:create';

    protected $description = 'Создание нового администратора.';

    /**
     * @throws Throwable
     */
    public function handle(AdminService $service, PermissionService $permissionService): int
    {
        $args = [
            'name' => $this->ask(self::QUESTION_NAME),
            'email' => $this->ask(self::QUESTION_EMAIL),
            'password' => $this->ask(self::QUESTION_PASSWORD),
        ];

        $validator = $this->validation($args);

        if ($validator->fails()) {
            $this->info('Staff User not created. See error messages below:');

            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $admin = $service->create(AdminDto::byArgs($args));

        $admin->assignRole(
            $permissionService->firstOrCreateSuperAdminRole()
        );

        $this->info("Администратор создан.");

        return self::SUCCESS;
    }

    protected function validation(array $args): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $args,
            [
                'name' => ['required', 'string', 'min:3'],
                'email' => ['required', 'email', 'unique:admins,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );
    }
}
