<?php

namespace Database\Seeders;

use App\Console\Commands\Import\PaymentDescription;
use Database\Seeders\Catalog\Features\FeatureMetricsSeeder;
use Database\Seeders\Catalog\Products\ProductUnitSeeder;
use Database\Seeders\Catalog\Solutions\SolutionSettingSeeder;
use Database\Seeders\News\TagsSeeder;
use Database\Seeders\Roles\DealerDefaultRoleSeeder;
use Database\Seeders\Roles\ModeratorRoleSeeder;
use Database\Seeders\Roles\SuperAdminRoleSeeder;
use Database\Seeders\Roles\TechnicianDefaultRoleSeeder;
use Database\Seeders\Roles\UserDefaultRoleSeeder;
use Database\Seeders\Stores\DistributorSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(
            [
                SuperAdminRoleSeeder::class,
                ModeratorRoleSeeder::class,
                UserDefaultRoleSeeder::class,
                TechnicianDefaultRoleSeeder::class,
                DealerDefaultRoleSeeder::class,
                CatalogSeeder::class,
                OrderCategorySeeder::class,
                OrderDeliveryTypeSeeder::class,
                TagsSeeder::class,
                FeatureMetricsSeeder::class,
                SolutionSettingSeeder::class,
                //                SolutionDemoSeeder::class,
                DistributorSeeder::class,
                ProductUnitSeeder::class,
                CountrySeeder::class,
            ]
        );

        Artisan::call(PaymentDescription::class);

        foreach (glob(base_path('app/Console/Commands/FixDB'). "/*.php") as $filename) {
            $name = substr(last(explode('/', $filename)),0, -4);
            Artisan::call('App\Console\Commands\FixDB\\' . $name);
        }
    }
}
