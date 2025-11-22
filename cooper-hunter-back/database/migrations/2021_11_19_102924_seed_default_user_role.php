<?php

use Database\Seeders\Roles\UserDefaultRoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->getDefaultUserRoleSeeder()->run();
    }

    protected function getDefaultUserRoleSeeder(): UserDefaultRoleSeeder
    {
        return app(UserDefaultRoleSeeder::class);
    }

    public function down(): void
    {
        $this->getDefaultUserRoleSeeder()->clear();
    }
};
