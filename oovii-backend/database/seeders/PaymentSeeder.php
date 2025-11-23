<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Throwable;
use WezomCms\Orders\Drivers\Payment\Bonus;
use WezomCms\Orders\Drivers\Payment\PayBox;
use WezomCms\Orders\Models\Payment;
use WezomCms\Orders\Models\PaymentTranslation;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        if (!$payments = Payment::count()) {
            try {
                DB::transaction(function () {
                    $data = $this->getData();

                    foreach ($data as $sort => $item) {
                        $model = new Payment();
                        $model->sort = $sort;
                        $model->driver = data_get($item, 'driver');
                        $model->save();

                        foreach ($item['translates'] as $lang => $trans) {
                            $translation = new PaymentTranslation();
                            $translation->locale = $lang;
                            $translation->name = $trans['name'];
                            $translation->payment_id = $model->id;
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
                'driver' => PayBox::KEY,
                'translates' => [
                    'ru' => [
                        'name' => 'PayBox Money (картой)',
                    ],
                    'kk' => [
                        'name' => 'PayBox Money (картой)',
                    ],
                ],
            ],
            [
                'driver' => Bonus::KEY,
                'translates' => [
                    'ru' => [
                        'name' => 'Бонусы',
                    ],
                    'kk' => [
                        'name' => 'Бонусы',
                    ],
                ],
            ],
        ];
    }
}
