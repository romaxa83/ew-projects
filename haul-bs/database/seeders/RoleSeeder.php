<?php

namespace Database\Seeders;

use App\Console\Commands\Syncs\RolesAndPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call(RolesAndPermissions::class);
    }
}
