<?php

namespace Database\Seeders\Catalog\Features;

use App\Imports\Catalog\FeatureMetricsImport;
use App\Services\Excel\Excel;
use Illuminate\Database\Seeder;

class FeatureMetricsSeeder extends Seeder
{
    public function run(): void
    {
        Excel::import(new FeatureMetricsImport(), database_path('files/product_properties_metrics.xlsx'));
    }
}
