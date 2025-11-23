<?php

namespace WezomCms\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Orders\Drivers\Delivery\Courier;
use WezomCms\Orders\Drivers\Delivery\NovaPoshtaCourier;
use WezomCms\Orders\Drivers\Delivery\NovaPoshtaPostal;
use WezomCms\Orders\Models\Delivery;

class DeliveriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'Адресная доставка',
                'driver' => Courier::KEY,
            ],
            [
                'name' => 'Курьер Новой Почты',
                'driver' => NovaPoshtaCourier::KEY,
            ],
            [
                'name' => 'Отделение Новой Почты',
                'driver' => NovaPoshtaPostal::KEY,
            ],
        ];

        foreach ($data as $index => $datum) {
            $obj = new Delivery();
            $obj->sort = $index;
            $obj->published = true;
            $obj->driver = $datum['driver'];

            $translatable = [];

            foreach (app('locales') as $locale => $language) {
                $translatable[$locale] = [
                    'name' => $datum['name'],
                ];
            }

            $obj->fill($translatable);
            $obj->save();
        }
    }
}
