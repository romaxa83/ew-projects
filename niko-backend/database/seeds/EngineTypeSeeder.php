<?php

class EngineTypeSeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('car_engine_types')->truncate();
        \DB::table('car_engine_type_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        try {
            \DB::transaction(function (){

                // link for payment
                foreach ($this->getData() as $item){
                    $l = new \WezomCms\Cars\Models\EngineType();
                    $l->save();

                    foreach ($item['translations'] as $lang => $tran){
                        $t = new \WezomCms\Cars\Models\EngineTypeTranslation();
                        $t->locale = $lang;
                        $t->engine_type_id = $l->id;
                        $t->name = $tran['name'];
                        $t->save();
                    }
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
                'translations' => [
                    'ru' => [
                        'name' => 'Бензин'
                    ],
                    'uk' => [
                        'name' => 'Бензин'
                    ]
                ]
            ],
            [
                'translations' => [
                    'ru' => [
                        'name' => 'Дизель'
                    ],
                    'uk' => [
                        'name' => 'Дизель'
                    ]
                ]
            ],
            [
                'translations' => [
                    'ru' => [
                        'name' => 'Электро'
                    ],
                    'uk' => [
                        'name' => 'Електро'
                    ]
                ]
            ],
            [
                'translations' => [
                    'ru' => [
                        'name' => 'Гибрид'
                    ],
                    'uk' => [
                        'name' => 'Гібрид'
                    ]
                ]
            ],
        ];
    }
}

