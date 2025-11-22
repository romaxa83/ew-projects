<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ListsSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(PricingPlanSeeder::class);
        $this->call(DefaultCompanySeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(TranslatesTableSeeder::class);
        $this->call(SuperAdminPermissionsTableSeeder::class);
        $this->call(SuperAdminSeeder::class);
        $this->call(BSSuperAdminSeeder::class);
    }
}
