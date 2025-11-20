<?php

class TransmissionsSeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('car_transmissions')->truncate();
        \DB::table('car_transmission_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        try {
            \DB::transaction(function (){

                // link for payment
                foreach ($this->getData() as $item){
                    $l = new \WezomCms\Cars\Models\Transmission();
                    $l->save();

                    foreach ($item['translations'] as $lang => $tran){
                        $t = new \WezomCms\Cars\Models\TransmissionTranslation();
                        $t->locale = $lang;
                        $t->transmission_id = $l->id;
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
                        'name' => 'Механическая'
                    ],
                    'uk' => [
                        'name' => 'Механічна'
                    ]
                ]
            ],
            [
                'translations' => [
                    'ru' => [
                        'name' => 'Автоматическая'
                    ],
                    'uk' => [
                        'name' => 'Автоматична'
                    ]
                ]
            ],
        ];
    }
}
