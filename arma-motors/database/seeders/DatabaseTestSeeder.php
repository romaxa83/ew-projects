<?php

namespace Database\Seeders;

use App\Repositories\User\LoyaltyRepository;
use Illuminate\Database\Seeder;

class DatabaseTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LanguageSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(RegionsSeeder::class);
        $this->call(BrandModelSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(DealershipSeeder::class);
        $this->call(PrivilegesSeeder::class);
        $this->call(TransportTypeSeeder::class);
        $this->call(DriverAgeSeeder::class);
        $this->call(InsuranceFranchiseSeeder::class);
        $this->call(DurationServiceSeeder::class);
        $this->call(PageSeeder::class);
        $this->call(TransmissionSeeder::class);
        $this->call(SparesGroupSeeder::class);
        $this->call(EngineVolumeSeeder::class);
        $this->call(MileageSeeder::class);
        $this->call(DriveUnitSeeder::class);
        $this->call(FuelSeeder::class);
        $this->call(WorkSeeder::class);
        $this->call(LoyaltySeeder::class);
        $this->call(OrderCarStatusSeeder::class);

        $this->call(SparesSeeder::class);
    }
}
