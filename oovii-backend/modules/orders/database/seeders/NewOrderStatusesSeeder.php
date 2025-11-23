<?php

namespace WezomCms\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Orders\Models\OrderStatus;

/**
 * Class NewOrderStatusesSeeder
 * @package WezomCms\Orders\Database\Seeders
 */
class NewOrderStatusesSeeder extends Seeder
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
                    'notification_text' => $datum['notification_text'],
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
                'notification_text' => 'Создан новый заказ',
            ],
            [
                'id' => OrderStatus::DONE,
                'name' => 'Завершен',
                'notification_text' => 'Заказ завершен',
            ],
            [
                'id' => OrderStatus::CANCELED,
                'name' => 'Отменен',
                'notification_text' => 'Заказ отменен',
            ],
            [
                'id' => OrderStatus::READY,
                'name' => 'Готов к отправке',
                'notification_text' => 'Заказ готов к отправке',
            ],
            [
                'id' => OrderStatus::PAID,
                'name' => 'Оплачен',
                'notification_text' => 'Заказ оплачен',
            ],
            [
                'id' => OrderStatus::SENT,
                'name' => 'Отправлен',
                'notification_text' => 'Заказ отправлен',
            ],
        ];
    }
}
