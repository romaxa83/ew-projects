<?php

namespace WezomCms\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Orders\Drivers\Payment\LiqPay;
use WezomCms\Orders\Models\Payment;

class PaymentsSeeder extends Seeder
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
                'name' => 'При получении',
            ],
            [
                'name' => 'LiqPay',
                'driver' => LiqPay::KEY,
            ],
            [
                'name' => 'Банковский перевод',
            ],
        ];

        foreach ($data as $index => $datum) {
            $obj = new Payment();
            $obj->sort = $index;
            $obj->published = true;
            $obj->driver = $datum['driver'] ?? null;

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
