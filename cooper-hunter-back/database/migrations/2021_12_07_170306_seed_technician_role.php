<?php

use Database\Seeders\Roles\TechnicianDefaultRoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->getSeeder()->run();
    }

    protected function getSeeder(): TechnicianDefaultRoleSeeder
    {
        return app(TechnicianDefaultRoleSeeder::class);
    }

    public function down(): void
    {
        $this->getSeeder()->clear();
    }
};
