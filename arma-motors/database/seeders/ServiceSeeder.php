<?php

namespace Database\Seeders;

use App\DTO\Catalog\Service\ServiceDTO;
use App\Models\Catalogs\Service\Service;
use App\Services\Catalog\Service\ServiceService;

class ServiceSeeder extends BaseSeeder
{

    public function __construct(protected ServiceService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('services')->truncate();
        \DB::table('service_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = ServiceDTO::byArgs($item);
                    $this->service->create($dto);
                }
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    protected function data(): array
    {
        return [
            [
                'sort' => 1,
                'alias' => Service::SERVICE_ALIAS,
                'icon' => 'service',
                'forGuest' => true,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'сервис',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'сервис',
                    ],
                ]
            ],
            [
                'sort' => 2,
                'alias' => Service::BODY_ALIAS,
                'icon' => 'kuz',
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'кузовной ремонт',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'кузовной ремонт',
                    ],
                ]
            ],
            [
                'sort' => 3,
                'alias' => Service::INSURANCE_ALIAS,
                'icon' => 'strah',
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'страхование',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'страхование',
                    ],
                ]
            ],
            [
                'sort' => 4,
                'alias' => Service::CREDIT_ALIAS,
                'icon' => 'kred',
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'кредитование',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'кредитование',
                    ],
                ]
            ],
            [
                'sort' => 5,
                'alias' => Service::SPARES_ALIAS,
                'icon' => 'zap',
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'заказ запчастей',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'заказ запчастей',
                    ],
                ]
            ],
            [
                'sort' => 6,
                'alias' => 'to',
                'parentId' => 1,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'то',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'то',
                    ],
                ]
            ],
            [
                'sort' => 7,
                'alias' => 'diagnostic',
                'parentId' => 1,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'диагностика',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'диагностика',
                    ],
                ]
            ],
            [
                'sort' => 8,
                'alias' => 'tire',
                'parentId' => 1,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'шиномантаж',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'шиномантаж',
                    ],
                ]
            ],
            [
                'sort' => 9,
                'alias' => 'other',
                'parentId' => 1,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'другие работы',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'другие работы',
                    ],
                ]
            ],
            [
                'sort' => 10,
                'alias' => 'go',
                'parentId' => 3,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'го',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'го',
                    ],
                ]
            ],
            [
                'sort' => 11,
                'alias' => 'dgo',
                'parentId' => 3,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'дго',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'дго',
                    ],
                ]
            ],
            [
                'sort' => 12,
                'alias' => 'casco',
                'parentId' => 3,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'каско',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'каско',
                    ],
                ]
            ],
        ];
    }
}

