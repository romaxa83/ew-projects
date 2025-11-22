<?php

namespace App\Console\Commands\Admins;

use App\Dto\Admins\AdminDto;
use App\Enums\Permissions\AdminRolesEnum;
use App\Models\Admins\Admin;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Services\Admins\AdminService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateAdminCommand extends Command
{
    public const QUESTION_FIRST_NAME = 'First name: ';
    public const QUESTION_LAST_NAME = 'Last name: ';
    public const QUESTION_EMAIL = 'Email: ';
    public const QUESTION_PHONE = 'Phone: ';
    public const QUESTION_PASSWORD = 'Password: ';

    protected $signature = 'admin:create';

    protected $description = 'Создание нового администратора.';

    /**
     * @param AdminService $service
     */
    public function handle(AdminService $service): void
    {
        $args = [
            'first_name' => $this->ask(self::QUESTION_FIRST_NAME),
            'last_name' => $this->ask(self::QUESTION_LAST_NAME),
            'email' => $this->ask(self::QUESTION_EMAIL),
            'phone' => $this->ask(self::QUESTION_PHONE),
            'password' => $this->ask(self::QUESTION_PASSWORD),
            'role_id' => Role::whereName(AdminRolesEnum::SUPER_ADMIN)
                ->first()->id,
            'language' => Language::default()
                ->first()->slug,
        ];

        $this->validation($args);

        $args['phones'] = [
            [
                'phone' => $args['phone'],
                'is_default' => true,
            ]
        ];

        $service->create(AdminDto::byArgs($args));

        $this->info("Администратор создан.");
    }

    protected function validation(array $args): void
    {
        Validator::validate(
            $args,
            [
                'first_name' => [
                    'required',
                    'string',
                ],
                'last_name' => [
                    'required',
                    'string'
                ],
                'email' => [
                    'required',
                    'email',
                    Rule::unique(Admin::class, 'email')
                ],
                'phone' => [
                    'required',
                    'string',
                    'regex:/^380[1-9][0-9]{8}$/',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8'
                ],
            ]
        );
    }
}
