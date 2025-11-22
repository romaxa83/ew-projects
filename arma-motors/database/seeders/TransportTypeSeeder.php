<?php

namespace Database\Seeders;

use App\DTO\Catalog\Car\TransportTypeDTO;
use App\Services\Catalog\Car\TransportTypeService;

class TransportTypeSeeder extends BaseSeeder
{

    public function __construct(protected TransportTypeService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('transport_types')->truncate();
        \DB::table('transport_type_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = TransportTypeDTO::byArgs($item);
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
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Легковой авто до 1600 куб.см (Включительно)',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Легковой авто до 1600 куб.см (Включительно)',
                    ],
                ]
            ],
            [
                'sort' => 2,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Легковой авто до 1601-2000 куб.см (Включительно)',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Легковой авто до 1601-2000 куб.см (Включительно)',
                    ],
                ]
            ],
            [
                'sort' => 3,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Легковой авто до 2001-2500 куб.см (Включительно)',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Легковой авто до 2001-2500 куб.см (Включительно)',
                    ],
                ]
            ],
            [
                'sort' => 4,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Легковой авто до 2501-3000 куб.см (Включительно)',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Легковой авто до 2501-3000 куб.см (Включительно)',
                    ],
                ]
            ],
            [
                'sort' => 5,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Легковой авто свыше 3000 куб.см',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Легковой авто свыше 3000 куб.см',
                    ],
                ]
            ],
            [
                'sort' => 6,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Легковой авто (исключительно с силовым электродвигателем, кроме гибридных авто)',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Легковой авто (исключительно с силовым электродвигателем, кроме гибридных авто)',
                    ],
                ]
            ],
            [
                'sort' => 7,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Прицепы к легковым авто',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Прицепы к легковым авто',
                    ],
                ]
            ]
        ];
    }
}


