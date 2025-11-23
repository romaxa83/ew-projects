<?php

use Illuminate\Database\Migrations\Migration;
use WezomCms\Orders\Models\OrderStatus;

class AddSystemOrderStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Artisan::call(
            'db:seed',
            [
                '--class' => \WezomCms\Orders\Database\Seeders\OrderStatusesSeeder::class,
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
    }
}
