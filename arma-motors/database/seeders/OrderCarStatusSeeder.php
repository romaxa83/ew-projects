<?php

namespace Database\Seeders;

use App\DTO\Catalog\Calc\MileageDTO;
use App\Models\User\OrderCar\OrderCarStatus;
use App\Models\User\OrderCar\OrderStatus;
use App\Models\User\OrderCar\OrderStatusTranslation;
use App\Services\Catalog\Calc\MileageService;

class OrderCarStatusSeeder extends BaseSeeder
{

    public function __construct()
    {
        parent::__construct();
    }

    public function run(): void
    {

        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table(OrderStatus::TABLE_NAME)->truncate();
        \DB::table(OrderStatusTranslation::TABLE)->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        if(OrderStatus::count() == 0){
            $data = $this->data();
            try {
                \DB::transaction(function () use ($data) {
                    foreach ($data as $k => $item){
                        $m = new OrderStatus();
                        $m->sort = $k + 1;
                        if(isset($item['for_front'])){
                            $m->for_front = $item['for_front'];
                        }
                        $m->save();

                        foreach ($item['translation'] as $lang => $name){
                            $t = new OrderStatusTranslation();
                            $t->model_id = $m->id;
                            $t->lang = $lang;
                            $t->name = $name;
                            $t->save();
                        }
                    }
                });

                // добавляем 9 статус если его нет
                $items = \DB::table('user_car_order_statuses as status')
                    ->selectRaw("status.order_car_id, COUNT(*)")
                    ->groupBy('status.order_car_id')
                    ->havingRaw("COUNT(*) <= 8")
                    ->get();

                foreach ($items as $one){
                    $s = new OrderCarStatus();
                    $s->order_car_id = $one->order_car_id;
                    $s->status_id = 9;
                    $s->status = OrderCarStatus::STATUS_WAIT;
                    $s->save();
                }

            } catch (\Throwable $e) {
                dd($e->getMessage());
            }
        }
    }

    protected function data(): array
    {
        return [
            [
                'translation' => [
                    'ru' => 'Оформлен заказ',
                    'uk' => 'Оформлен заказ'
                ]
            ],
            [
                'translation' => [
                    'ru' => 'Сформирован VIN номер',
                    'uk' => 'Сформирован VIN номер'
                ]
            ],
            [
                'translation' => [
                    'ru' => 'Авто в производстве',
                    'uk' => 'Авто в производстве'
                ]
            ],
            [
                'translation' => [
                    'ru' => 'Авто произведено',
                    'uk' => 'Авто произведено'
                ]
            ],
            [
                'translation' => [
                    'ru' => 'Авто направлено в Украину',
                    'uk' => 'Авто направлено в Украину'
                ]
            ],
            [
                'translation' => [
                    'ru' => 'Авто на складе в Украине',
                    'uk' => 'Авто на складе в Украине'
                ]
            ],
            [
                'translation' => [
                    'ru' => 'Авто направляется в ДЦ',
                    'uk' => 'Авто направляется в ДЦ'
                ]
            ],
            [
                'translation' => [
                    'ru' => 'Авто готово к выдаче',
                    'uk' => 'Авто готово к выдаче'
                ]
            ],
            [
                'for_front' => false,
                'translation' => [
                    'ru' => 'Сделать обычным авто',
                    'uk' => 'Сделать обычным авто'
                ]
            ],
        ];
    }
}





