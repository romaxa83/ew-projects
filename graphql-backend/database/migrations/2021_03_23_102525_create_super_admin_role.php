<?php

use Database\Seeders\SuperAdminRoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->getSuperAdminRoleSeeder()->run();
    }

    protected function getSuperAdminRoleSeeder(): SuperAdminRoleSeeder
    {
        return app(SuperAdminRoleSeeder::class);
    }

    public function down(): void
    {
        $this->getSuperAdminRoleSeeder()->clear();
    }
};
