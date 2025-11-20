<?php

class ServicesSeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('service_groups')->truncate();
        \DB::table('service_group_translations')->truncate();
        \DB::table('services')->truncate();
        \DB::table('service_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        try {
            \DB::transaction(function (){

                // link for payment
                foreach ($this->getData() as $item){
                    $sg = new \WezomCms\Services\Models\ServiceGroup();
                    $sg->type = $item['type'];
                    $sg->sort = $item['sort'];
                    if(isset($item['published'])){
                        $sg->published = $item['published'];
                    }
                    $sg->save();

                    foreach ($item['translations'] as $lang => $tran){
                        $t = new \WezomCms\Services\Models\ServiceGroupTranslation();
                        $t->locale = $lang;
                        $t->service_group_id = $sg->id;
                        $t->name = $tran['name'];
                        $t->slug = $tran['slug'];
                        $t->save();
                    }

                    foreach ($item['services'] ?? [] as $service){
                        $s = new \WezomCms\Services\Models\Service();
                        $s->sort = $service['sort'];
                        $s->service_group_id = $sg->id;
                        $s->save();

                        foreach ($service['translations'] as $lang => $tran){
                            $t = new \WezomCms\Services\Models\ServiceTranslation();
                            $t->locale = $lang;
                            $t->service_id = $s->id;
                            $t->name = $tran['name'];
                            $t->save();
                        }
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
                'type' => \WezomCms\Services\Types\ServiceType::TYPE_STO,
                'sort' => 1,
                'translations' => [
                    'ru' => [
                        'name' => 'СТО',
                        'slug' => 'sto',
                    ],
                    'uk' => [
                        'name' => 'СТО',
                        'slug' => 'sto'
                    ]
                ],
                'services' => [
                    [
                        'sort' => 1,
                        'translations' => [
                            'ru' => [
                                'name' => 'ТО'
                            ],
                            'uk' => [
                                'name' => 'ТО'
                            ]
                        ]
                    ],
                    [
                        'sort' => 2,
                        'translations' => [
                            'ru' => [
                                'name' => 'Ремонт/Сервис'
                            ],
                            'uk' => [
                                'name' => 'Ремонт/Сервіс'
                            ]
                        ]
                    ],
                    [
                        'sort' => 3,
                        'translations' => [
                            'ru' => [
                                'name' => 'Кузовной ремонт'
                            ],
                            'uk' => [
                                'name' => 'Кузовний ремонт'
                            ]
                        ]
                    ],
                    [
                        'sort' => 4,
                        'translations' => [
                            'ru' => [
                                'name' => 'Шиномонтаж'
                            ],
                            'uk' => [
                                'name' => 'Шиномонтаж'
                            ]
                        ]
                    ],
                ]
            ],
            [
                'type' => \WezomCms\Services\Types\ServiceType::TYPE_TRADE_IN,
                'sort' => 2,
                'translations' => [
                    'ru' => [
                        'name' => 'Trade in',
                        'slug' => 'trade-in',
                    ],
                    'uk' => [
                        'name' => 'Trade in',
                        'slug' => 'trade-in'
                    ]
                ]
            ],
            [
                'type' => \WezomCms\Services\Types\ServiceType::TYPE_TEST_DRIVE,
                'sort' => 3,
                'translations' => [
                    'ru' => [
                        'name' => 'Тест драйв',
                        'slug' => 'test-drive',
                    ],
                    'uk' => [
                        'name' => 'Тест драйв',
                        'slug' => 'test-drive'
                    ]
                ]
            ],
            [
                'type' => \WezomCms\Services\Types\ServiceType::TYPE_SPARES,
                'sort' => 4,
                'translations' => [
                    'ru' => [
                        'name' => 'Заказ запчастей',
                        'slug' => 'zakaz-zapchastei',
                    ],
                    'uk' => [
                        'name' => 'Замовлення запчастин',
                        'slug' => 'zakaz-zapchastei'
                    ]
                ]
            ],
            [
                'type' => \WezomCms\Services\Types\ServiceType::TYPE_REPAIRS,
                'sort' => 6,
                'published' => false,
                'translations' => [
                    'ru' => [
                        'name' => 'Ремонт кузова',
                        'slug' => 'remont',
                    ],
                    'uk' => [
                        'name' => 'Ремонт кузова',
                        'slug' => 'remont'
                    ]
                ]
            ],
            [
                'type' => \WezomCms\Services\Types\ServiceType::TYPE_INSURANCE,
                'sort' => 5,
                'translations' => [
                    'ru' => [
                        'name' => 'Заказ страховки',
                        'slug' => 'zakaz-strahovki',
                    ],
                    'uk' => [
                        'name' => 'Замовлення страховки',
                        'slug' => 'zakaz-strahovki'
                    ]
                ],
                'services' => [
                    [
                        'sort' => 5,
                        'translations' => [
                            'ru' => [
                                'name' => 'ГО'
                            ],
                            'uk' => [
                                'name' => 'ГО'
                            ]
                        ]
                    ],
                    [
                        'sort' => 6,
                        'translations' => [
                            'ru' => [
                                'name' => 'Каско'
                            ],
                            'uk' => [
                                'name' => 'Каско'
                            ]
                        ]
                    ],
                ]
            ],
        ];
    }
}
