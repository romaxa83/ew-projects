<?php

namespace Database\Seeders;

use App\DTO\Catalog\Service\PrivilegesDTO;
use App\Services\Catalog\Service\PrivilegesService;

class PrivilegesSeeder extends BaseSeeder
{

    public function __construct(protected PrivilegesService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('privileges')->truncate();
        \DB::table('privileges_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = PrivilegesDTO::byArgs($item);
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
                        'name' => 'Без льгот',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Без льгот',
                    ],
                ]
            ],
            [
                'sort' => 2,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Инвалидность 2 категория',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Инвалидность 2 категория',
                    ],
                ]
            ],
            [
                'sort' => 3,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Пенсионер',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Пенсионер',
                    ],
                ]
            ],
            [
                'sort' => 4,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Участник войны',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Участник войны',
                    ],
                ]
            ],
            [
                'sort' => 5,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Чернобылец (1-2 категория)',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Чернобылец (1-2 категория)',
                    ],
                ]
            ]
        ];
    }
}


