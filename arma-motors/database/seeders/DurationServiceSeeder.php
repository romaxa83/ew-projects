<?php

namespace Database\Seeders;

use App\DTO\Catalog\Service\DurationDTO;
use App\Services\Catalog\Service\DurationService;

class DurationServiceSeeder extends BaseSeeder
{

    public function __construct(protected DurationService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('service_durations')->truncate();
        \DB::table('service_duration_translations')->truncate();
        \DB::table('service_duration_service_relation')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = DurationDTO::byArgs($item);
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
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '15 дней',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '15 днів',
                    ],
                ]
            ],
            [
                'sort' => 2,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '1 месяц',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '1 місяць',
                    ],
                ]
            ],
            [
                'sort' => 3,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '2 месяца',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '2 місяці',
                    ],
                ]
            ],
            [
                'sort' => 4,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '3 месяца',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '3 місяці',
                    ],
                ]
            ],
            [
                'sort' => 5,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '4 месяца',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '4 місяці',
                    ],
                ]
            ],
            [
                'sort' => 6,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '5 месяцев',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '5 місяців',
                    ],
                ]
            ],
            [
                'sort' => 7,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '6 месяцев',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '6 місяців',
                    ],
                ]
            ],
            [
                'sort' => 8,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '7 месяцев',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '7 місяців',
                    ],
                ]
            ],
            [
                'sort' => 9,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '8 месяцев',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '8 місяців',
                    ],
                ]
            ],
            [
                'sort' => 10,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '9 месяцев',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '9 місяців',
                    ],
                ]
            ],
            [
                'sort' => 11,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '10 месяцев',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '10 місяців',
                    ],
                ]
            ],
            [
                'sort' => 12,
                'serviceIds' => [3],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '11 месяцев',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '11 місяців',
                    ],
                ]
            ],
            [
                'sort' => 13,
                'serviceIds' => [3, 4],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '1 год',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '1 рік',
                    ],
                ]
            ],
            [
                'sort' => 14,
                'serviceIds' => [4],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '2 года',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '2 роки',
                    ],
                ]
            ],
            [
                'sort' => 15,
                'serviceIds' => [4],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '3 года',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '3 роки',
                    ],
                ]
            ],
            [
                'sort' => 16,
                'serviceIds' => [4],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '4 года',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '4 роки',
                    ],
                ]
            ],
            [
                'sort' => 17,
                'serviceIds' => [4],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '5 лет',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '5 років',
                    ],
                ]
            ],
            [
                'sort' => 18,
                'serviceIds' => [4],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '6 лет',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '6 років',
                    ],
                ]
            ],
            [
                'sort' => 19,
                'serviceIds' => [4],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => '7 лет',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => '7 років',
                    ],
                ]
            ]
        ];
    }
}




