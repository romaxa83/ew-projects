<?php

use WezomCms\Promotions\Models\Promotions;
use WezomCms\Promotions\Models\PromotionsTranslation;

class PromotionSeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('promotions')->truncate();
        \DB::table('promotions_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        try {
            \DB::transaction(function (){

                for($i = 1; $i != 10; $i++){

                    if($i > 5){
                        factory(Promotions::class)->create([
                            'sort' => $i,
                            'type' => Promotions::TYPE_INDIVIDUAL,
                            'link' => $this->getFaker()->url,
                            'code_1c' => random_int(10000, 99999)
                        ]);
                    } else {
                        factory(Promotions::class)->create([
                            'sort' => $i,
                            'type' => Promotions::TYPE_COMMON,
                            'link' => $this->getFaker()->url
                        ]);
                    }

                    factory(PromotionsTranslation::class)->create(['locale' => 'ru', 'promotions_id' => $i]);
                    factory(PromotionsTranslation::class)->create(['locale' => 'uk', 'promotions_id' => $i]);
                }
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }
}


