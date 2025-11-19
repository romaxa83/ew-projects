<?php

declare(strict_types=1);

namespace Wezom\Admins\Services;

use Wezom\Admins\Dto\AdminDto;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;

class AdminService
{
    public function create(AdminDto $dto, string|AdminStatusEnum $status): Admin
    {
        $admin = new Admin();

        $admin->status = $status;
        $admin->active = true;
        $admin->email = $dto->email;

        $this->fill($admin, $dto);

        return $admin;
    }

    public function update(Admin $admin, AdminDto $dto): Admin
    {
        $this->fill($admin, $dto);

        return $admin;
    }

    public function fill(Admin $admin, AdminDto $dto): void
    {
        $admin->fill($dto->all());

        $admin->save();

        $admin->syncRoles($dto->roleId);
    }

    public function changePassword(Admin $admin, string $password): bool
    {
        return $admin
            ->setPassword($password)
            ->save();
    }
}
