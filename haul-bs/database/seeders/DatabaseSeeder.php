<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            RoleSeeder::class,
            InventoryCategorySeeder::class,
        ]);

        if(!App::environment('testing')){
            foreach (glob(base_path('app/Console/Commands/FixDB'). "/*.php") as $filename) {
                $name = substr(last(explode('/', $filename)),0, -4);
                Artisan::call('App\Console\Commands\FixDB\\' . $name);
            }
        }
    }
}
