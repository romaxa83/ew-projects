<?php

use Illuminate\Database\Migrations\Migration;
use WezomCms\Orders\Models\OrderStatus;

class RecreateSystemOrderStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table(OrderStatus::TABLE)->delete();

        Artisan::call(
            'db:seed',
            [
                '--class' => \WezomCms\Orders\Database\Seeders\NewOrderStatusesSeeder::class,
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table(OrderStatus::TABLE)->delete();

        Artisan::call(
            'db:seed',
            [
                '--class' => \WezomCms\Orders\Database\Seeders\OrderStatusesSeeder::class,
            ]
        );
    }
}
