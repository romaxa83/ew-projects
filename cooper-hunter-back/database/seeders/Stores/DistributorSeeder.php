<?php

namespace Database\Seeders\Stores;

use App\Models\Stores\Distributor;
use App\Services\Stores\DistributorsImportService;
use Illuminate\Database\Seeder;

class DistributorSeeder extends Seeder
{
    public function run(): void
    {
        if (Distributor::query()->exists()) {
            return;
        }

        resolve(DistributorsImportService::class)->seed();
    }
}