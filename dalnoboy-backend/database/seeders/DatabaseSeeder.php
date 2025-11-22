<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(
            [
                LanguageSeeder::class,
                PermissionsSeeder::class,
                RegionsSeeder::class,
                SuperAdminSeeder::class,
                UserSeeder::class,
                SchemaWheelSeeder::class,
                TireChangesReasonsSeeder::class,
                DictionariesSeeder::class,
            ]
        );
    }
}
