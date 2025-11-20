<?php

class LoyaltySeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('loyalties')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        try {
            \DB::transaction(function (){

                foreach ($this->getData() as $item){
                    $l = new \WezomCms\Users\Models\LoyaltyLevel();
                    $l->segment = $item['segment'];
                    $l->level = $item['level'];
                    $l->count_auto = $item['count_auto'];
                    $l->setSumServices($item['sum_services']);
                    $l->setDiscountSto($item['discount_sto']);
                    $l->setDiscountSpares($item['discount_spares']);

                    $l->save();
                }
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function getData()
    {
        return [
            [
                'level' => \WezomCms\Users\Types\LoyaltyLevel::WHITE,
                'segment' => 1,
                'count_auto' => 1,
                'sum_services' => 10000,
                'discount_sto' => 2,
                'discount_spares' => 2,
            ],
            [
                'level' => \WezomCms\Users\Types\LoyaltyLevel::BLACK,
                'segment' => 1,
                'count_auto' => 2,
                'sum_services' => 40000,
                'discount_sto' => 4,
                'discount_spares' => 3,
            ],
            [
                'level' => \WezomCms\Users\Types\LoyaltyLevel::SILVER,
                'segment' => 1,
                'count_auto' => 3,
                'sum_services' => 70000,
                'discount_sto' => 6,
                'discount_spares' => 4,
            ],
            [
                'level' => \WezomCms\Users\Types\LoyaltyLevel::GOLD,
                'segment' => 1,
                'count_auto' => 4,
                'sum_services' => 110000,
                'discount_sto' => 8,
                'discount_spares' => 5,
            ],
            [
                'level' => \WezomCms\Users\Types\LoyaltyLevel::PLATINUM,
                'segment' => 1,
                'count_auto' => 5,
                'sum_services' => 150000,
                'discount_sto' => 10,
                'discount_spares' => 7,
            ],
        ];
    }
}

