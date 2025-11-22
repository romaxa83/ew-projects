<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Truncate extends Command
{
    protected $signature = 'am:truncate-db';

    protected $description = 'Truncate table database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            foreach ($this->tables() as $table){
                \DB::table($table)->truncate();
                $this->warn("truncate - {$table}");
            }
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        } catch(\Throwable $e){
            $this->error($e->getMessage());
        }
    }

    private function tables(): array
    {
        return [
            'car_brands',
            'car_models',
            'calc_models',
            'calc_model_spares_pivot',
            'calc_model_work_pivot',
            'car_brand_mileage_relations',
            'car_brand_work_relations',
            'sms_verify',
            'dealerships',
            'dealership_translations',
            'dealership_departments',
            'dealership_department_translations',
            'dealership_department_schedules',
            'fcm_notifications',
            'files',
            'hashes',
            'images',
            'import_spares',
            'loyalties',
            'order_additions',
            'orders',
            'promotions',
            'promotion_user_relations',
            'promotion_translations',
            'spares',
            'spares_download_files',
            'spares_group_translations',
            'spares_groups',
            'user_car_confidants',
            'user_car_loyalty_pivot',
            'user_cars',
//            'users'
        ];
    }
}
