<?php

namespace WezomCms\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Orders\Models\OrderStatus;

/**
 * Class OrderStatusesSeeder
 * @package WezomCms\Orders\Database\Seeders
 */
class OrderStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $statuses = OrderStatus::all()->pluck('id')->toArray();

        foreach ($this->data() as $index => $datum) {
            if (in_array($datum['id'], $statuses, true)) {
                continue;
            }

            $obj = new OrderStatus();
            $obj->id = $datum['id'];
            $obj->sort = $index;

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

    private function data(): array
    {
        return [
            [
                'id' => OrderStatus::NEW,
                'name' => 'Новый',
            ],
            [
                'id' => OrderStatus::DONE,
                'name' => 'Завершен',
            ],
            [
                'id' => OrderStatus::CANCELED,
                'name' => 'Отменен',
            ],
            [
                'id' => OrderStatus::READY,
                'name' => 'Готов к отправке',
            ],
            [
                'id' => OrderStatus::PAID,
                'name' => 'Оплачен',
            ],
            [
                'id' => OrderStatus::SENT,
                'name' => 'Отправлен',
            ],
        ];
    }
}
