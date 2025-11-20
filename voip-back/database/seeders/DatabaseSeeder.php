<?php

namespace Database\Seeders;

use Database\Seeders\Demo\AdminSeeder;
use Database\Seeders\Roles\EmployeeRoleSeeder;
use Database\Seeders\Roles\SuperAdminRoleSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SuperAdminRoleSeeder::class,
            EmployeeRoleSeeder::class,
            ScheduleSeeder::class,
        ]);

        // for demo
        if(App::environment('local')){
            $this->call([
                AdminSeeder::class,
            ]);
        }

        if(!App::environment('testing')){
            foreach (glob(base_path('app/Console/Commands/FixDB'). "/*.php") as $filename) {
                $name = substr(last(explode('/', $filename)),0, -4);
                Artisan::call('App\Console\Commands\FixDB\\' . $name);
            }
        }
    }
}
