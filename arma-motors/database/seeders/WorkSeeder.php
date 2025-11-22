<?php

namespace Database\Seeders;

use App\DTO\Catalog\Calc\WorkDTO;
use App\Services\Catalog\Calc\WorkService;
class WorkSeeder extends BaseSeeder
{

    public function __construct(protected WorkService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('works')->truncate();
        \DB::table('work_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = WorkDTO::byArgs($item);
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
                'minutes' => 10,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Замена масла',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Заміна масла',
                    ],
                ]
            ],
            [
                'sort' => 2,
                'minutes' => 10,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Диагностика ходовой',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'діагностика ходової',
                    ],
                ]
            ],
            [
                'sort' => 3,
                'minutes' => 10,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Замена водоочистительного двигателя',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Заміна водоочисного двигуна',
                    ],
                ]
            ],
            [
                'sort' => 4,
                'minutes' => 10,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Техническое обслуживание',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Технічне обслуговування',
                    ],
                ]
            ],
            [
                'sort' => 5,
                'minutes' => 10,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Замена ремня',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Заміна ременя',
                    ],
                ]
            ],
            [
                'sort' => 6,
                'minutes' => 10,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Чистка салона',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Чистка салону',
                    ],
                ]
            ],
            [
                'sort' => 7,
                'minutes' => 10,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Замена фильтров',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Заміна фільтрів',
                    ],
                ]
            ]
        ];
    }
}



