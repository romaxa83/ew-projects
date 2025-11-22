<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if(App::environment('local')){
            $this->call([
                LanguageSeeder::class,
                RolesSeeder::class
            ]);
        }

        $this->call(PermissionsSeeder::class);

        if(App::environment('local')){
            $this->call([
                RegionsSeeder::class,
                BrandModelSeeder::class,
//                DealershipSeeder::class,
                ServiceSeeder::class,
                PrivilegesSeeder::class,
                TransportTypeSeeder::class,
                DriverAgeSeeder::class,
                InsuranceFranchiseSeeder::class,
                DurationServiceSeeder::class,
                PageSeeder::class,
                TransmissionSeeder::class,
                SparesGroupSeeder::class,
                EngineVolumeSeeder::class,
                MileageSeeder::class,
                DriveUnitSeeder::class,
                FuelSeeder::class,
                WorkSeeder::class,
                LoyaltySeeder::class,
                OrderCarStatusSeeder::class,
                SparesSeeder::class,
            ]);
        }
    }
}
