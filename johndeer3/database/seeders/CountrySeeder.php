<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('countries')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $countries = array_map('str_getcsv', file(__DIR__.'/_countries.csv'));

        try {
            \DB::transaction(function () use ($countries) {

                collect($countries)->each(function($country){

                    $model = new \App\Models\Country();
                    $model->name = $country[0];
                    $model->save();
                });
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }
}
