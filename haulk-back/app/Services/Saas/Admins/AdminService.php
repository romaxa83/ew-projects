<?php

namespace App\Services\Saas\Admins;

use App\Dto\Saas\Admins\AdminDto;
use App\Models\Admins\Admin;
use Exception;

class AdminService
{
    public function create(AdminDto $dto): Admin
    {
        $admin = new Admin();

        $this->fill($admin, $dto);

        $admin->save();

        return $admin;
    }

    private function fill(Admin $admin, AdminDto $dto): void
    {
        $admin->full_name = $dto->getFullName();
        $admin->email = $dto->getEmail();
        $admin->phone = $dto->getPhone();

        if ($password = $dto->getPassword()) {
            $admin->password = $password;
        }
    }

    public function update(Admin $admin, AdminDto $dto): Admin
    {
        $this->fill($admin, $dto);

        $admin->save();

        return $admin;
    }

    /**
     * @param Admin $admin
     * @throws Exception
     */
    public function delete(Admin $admin): void
    {
        $admin->delete();
    }
}
