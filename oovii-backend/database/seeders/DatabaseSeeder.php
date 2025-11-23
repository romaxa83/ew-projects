<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Catalog\Database\Seeders\SizeSpecificationsSeeder;
use WezomCms\Orders\Database\Seeders\OrderStatusesSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // $this->call(CompanySeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(DeliverySeeder::class);
        $this->call(OrderStatusesSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(LabelsSeeder::class);
        $this->call(SizeSpecificationsSeeder::class);
        $this->call(ProductReviewsSeeder::class);
    }
}
