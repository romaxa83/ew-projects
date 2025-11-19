<?php

namespace Wezom\Admins\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Str;
use Throwable;
use Wezom\Admins\Dto\AdminDto;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminService;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Models\Permission\Role;

class MakeSuperAdminCommand extends Command
{
    protected $signature = 'make:super-admin
                            {--name= : Administrator name}
                            {--email= : Administrator E-mail}
                            {--password= : Administrator password}';
    protected $description = 'Create super admin';

    /**
     * @throws Throwable
     */
    public function handle(AdminService $service): int
    {
        $roleId = Role::query()->forAdmins()->whereType(RoleEnum::SUPER_ADMIN)->firstOrFail()->id;

        try {
            $name = Str::of($this->getNameOption());

            $dto = new AdminDto(
                firstName: $name->before(' ')->trim(),
                lastName: $name->after(' ')->trim(),
                email: $this->getEmailOption(),
                phone: null,
                roleId: $roleId
            );

            $password = $this->getPasswordOption();
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return static::FAILURE;
        }

        $service->create($dto, AdminStatusEnum::ACTIVE)
            ->setPassword($password)
            ->save();

        $this->info('Admin created');

        return static::SUCCESS;
    }

    public function getNameOption(): string
    {
        return $this->option('name') ?? $this->ask('Please specify administrator name', 'Super Admin');
    }

    protected function getEmailOption(): string
    {
        $email = $this->option('email') ?? $this->ask('Please specify administrator email', 'admin@gmail.com');
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Email is incorrect!');
        }

        if (Admin::where('email', $email)->exists()) {
            throw new InvalidArgumentException('Email already exists in database.');
        }

        return $email;
    }

    protected function getPasswordOption(): string
    {
        $minLength = Admin::MIN_LENGTH_PASSWORD;
        $password = $this->option('password')
            ?? $this->secret(
                'Please specify password for: '
                . $this->option('email') .
                " Min length $minLength characters"
            );
        if (!$password) {
            throw new InvalidArgumentException('Password can`t be empty.');
        }

        if (mb_strlen($password) < $minLength) {
            throw new InvalidArgumentException("The password should be at least $minLength characters");
        }

        return $password;
    }
}
