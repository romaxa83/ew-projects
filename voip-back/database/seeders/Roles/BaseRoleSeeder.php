<?php

namespace Database\Seeders\Roles;

use Core\Services\Permissions\PermissionService;
use Illuminate\Database\Seeder;

class BaseRoleSeeder extends Seeder
{
    public function getPermissionService(): PermissionService
    {
        return app(PermissionService::class);
    }
}
