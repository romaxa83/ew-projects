<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Throwable;
use WezomCms\Orders\Drivers\Delivery\SdekCourier;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\DeliveryTranslation;
use WezomCms\Orders\Models\Payment;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        if (!$deliveries = Delivery::count()) {
            try {
                DB::transaction(function () {
                    $data = $this->getData();
                    $payments = Payment::all()->pluck('id')->toArray();

                    foreach ($data as $sort => $item) {
                        $model = new Delivery();
                        $model->sort = $sort;
                        $model->driver = data_get($item, 'driver');
                        $model->save();

                        $model->payments()->sync($payments);

                        foreach ($item['translates'] as $lang => $trans) {
                            $translation = new DeliveryTranslation();
                            $translation->locale = $lang;
                            $translation->name = $trans['name'];
                            $translation->delivery_id = $model->id;
                            $translation->save();
                        }
                    }
                });
            } catch (Throwable $e) {
                dd($e->getMessage());
            }
        }
    }

    protected function getData(): array
    {
        return [
            [
                'driver' => SdekCourier::KEY,
                'translates' => [
                    'ru' => [
                        'name' => 'СДЕК Курьер до двери',
                    ],
                    'kk' => [
                        'name' => 'СДЕК Курьер до двери',
                    ],
                ],
            ],
            /*[
                'driver' => NovaPoshtaCourier::KEY,
                'translates' => [
                    'ru' => [
                        'name' => 'Новая почта',
                    ],
                    'kk' => [
                        'name' => 'Новая почта',
                    ],
                ],
            ],*/
        ];
    }
}
