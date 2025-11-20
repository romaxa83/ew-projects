<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        $this->call(RegionsSeeder::class);
//        $this->call(CarsSeeder::class);
//        $this->call(SettingsSeeder::class);
//        $this->call(ServicesSeeder::class);
//        $this->call(TransmissionsSeeder::class);
//        $this->call(EngineTypeSeeder::class);
//        $this->call(DealershipsSeeder::class);
        $this->call(FcmNotificationSeeder::class);
//        $this->call(PromotionSeeder::class);
//        $this->call(LoyaltySeeder::class);

        // $this->call(UserSeeder::class);
    }
}
