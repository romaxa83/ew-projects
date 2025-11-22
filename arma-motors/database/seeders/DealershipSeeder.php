<?php

namespace Database\Seeders;

use App\DTO\Dealership\DealershipDTO;
use App\Models\Catalogs\Car\Brand;
use App\Models\Dealership\Department;
use App\Services\Dealership\DealershipService;

class DealershipSeeder extends BaseSeeder
{

    public function __construct(protected DealershipService $dealershipService)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('dealerships')->truncate();
        \DB::table('dealership_translations')->truncate();
        \DB::table('dealership_departments')->truncate();
        \DB::table('dealership_department_translations')->truncate();
        \DB::table('dealership_department_schedules')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = DealershipDTO::byArgs($item);
                    $this->dealershipService->create($dto);
                }

            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    protected function data(): array
    {
        $random = Brand::orderBy(\DB::raw('RAND()'))->first();;
        $renault = Brand::query()->where('name','Renault')->first();
        $mitsubishi = Brand::query()->where('name','Mitsubishi')->first();
        $volvo = Brand::query()->where('name','Volvo')->first();

        return [
            [
                'brandId' => $renault->id ?? $random->id,
                'website' => 'http://renault.ua',
                'lat' => '55.909090',
                'lon' => '56.909090',
                'alias' => 'http://renault.ua',
                'timeStep' => [
                    [
                        'serviceId' => 2,   // body
                        'step' => 1800000   // 0:30 h
                    ],
                    [
                        'serviceId' => 7,   // diagnostic
                        'step' => 7200000   // 2:00 h
                    ]
                ],
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Арма моторс Renault',
                        'text' => $this->getFaker()->text(300),
                        'address' => 'г. Киев, ул. Кольцевая дорога, 18',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Арма моторс Renault',
                        'text' => $this->getFaker()->text(300),
                        'address' => 'м. Київ, вул. Кільцева дорога, 18',
                    ],
                ],
                'departmentSales' => [
                    'phone' => '+389555555512',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'email' => 'department.sales.1@gmail.com',
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Отдел продаж',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Відділ продажу',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ],
                'departmentService' => [
                    'phone' => '+389555555512',
                    'email' => 'department.service.1@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Отдел сервиса',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Відділ сервісу',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ],
                'departmentCash' => [
                    'phone' => '+389555555522',
                    'email' => 'department.credit.1@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_CREDIT,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Отдел кредитования и страхования',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Відділ кредитування та страхування',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ],
                'departmentBody' => [
                    'phone' => '+389555555522',
                    'email' => 'department.body.1@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_BODY,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Кузовной отдел',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Кузовний відділ',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ]
            ],
            [
                'brandId' => $mitsubishi->id ?? $random->id,
                'website' => 'http://mitsubishi-motors.com',
                'lat' => '55.909090',
                'lon' => '56.909090',
                'alias' => 'mitsubishi-motors',
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Арма моторс Mitsubishi',
                        'text' => $this->getFaker()->text(300),
                        'address' => 'г. Киев, ул. Зодчих 5а',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Арма моторс Mitsubishi',
                        'text' => $this->getFaker()->text(300),
                        'address' => 'м. Київ, вул. Зодчих, 18',
                    ],
                ],
                'departmentSales' => [
                    'phone' => '+389555555512',
                    'email' => 'department.sales.2@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_SALES,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Отдел продаж',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Відділ продажу',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ],
                'departmentService' => [
                    'phone' => '+389555555512',
                    'email' => 'department.service.2@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_SERVICE,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Отдел сервиса',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Відділ сервісу',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ],
                'departmentCash' => [
                    'phone' => '+389555555512',
                    'email' => 'department.credit.2@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_CREDIT,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Отдел кредитования и страхования',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Відділ кредитування та страхування',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ],
                'departmentBody' => [
                    'phone' => '+389555555512',
                    'email' => 'department.body.2@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_BODY,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Кузовной отдел',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Кузовний відділ',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ],
            ],
            [
                'brandId' => $volvo->id ?? $random->id,
                'website' => 'http://volvocars.com',
                'lat' => '55.909090',
                'lon' => '56.909090',
                'alias' => 'volvocars',
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Викинг моторс Volvo',
                        'text' => $this->getFaker()->text(300),
                        'address' => 'г. Киев, ул. Кольцевая дорога, 18',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Викинг моторс Volvo',
                        'text' => $this->getFaker()->text(300),
                        'address' => 'м. Київ, вул. Кільцева дорога, 18',
                    ],
                ],
                'departmentSales' => [
                    'phone' => '+389555555512',
                    'email' => 'department.sales.3@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_SALES,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Отдел продаж',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Відділ продажу',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ],
                'departmentService' => [
                    'phone' => '+389555555512',
                    'email' => 'department.service.3@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_SERVICE,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Отдел сервиса',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Відділ сервісу',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                ],
                'departmentCash' => [
                    'phone' => '+389555555512',
                    'email' => 'department.credit.3@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_CREDIT,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Отдел кредитования и страхования',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Відділ кредитування та страхування',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ],
                    'schedule' => [
                        [
                            'day' => 1,
                            'from' => 28800000, // 8:00
                            'to' => 61200000 // 17:00
                        ],
                        [
                            'day' => 2,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 3,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 4,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 5,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 6,
                            'from' => 28800000,
                            'to' => 61200000
                        ],
                        [
                            'day' => 7,
                            'from' => null,
                            'to' => null
                        ]
                    ]
                ],
                'departmentBody' => [
                    'phone' => '+389555555512',
                    'email' => 'department.body.3@gmail.com',
                    'telegram' => 'telegram_link',
                    'viber' => 'viber_link',
                    'type' => Department::TYPE_BODY,
                    'lat' => '55.909090',
                    'lon' => '56.909090',
                    'translations' => [
                        [
                            'lang' => 'ru',
                            'name' => 'Кузовной отдел',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                        [
                            'lang' => 'uk',
                            'name' => 'Кузовний відділ',
                            'address' => $this->getFaker()->streetAddress,
                        ],
                    ]
                ],
                'schedule' => [
                    [
                        'day' => 1,
                        'from' => null, // 8:00
                        'to' => null // 17:00
                    ],
                    [
                        'day' => 2,
                        'from' => null,
                        'to' => null
                    ],
                    [
                        'day' => 3,
                        'from' => null,
                        'to' => null
                    ],
                    [
                        'day' => 4,
                        'from' => null,
                        'to' => null
                    ],
                    [
                        'day' => 5,
                        'from' => null,
                        'to' => null
                    ],
                    [
                        'day' => 6,
                        'from' => null,
                        'to' => null
                    ],
                    [
                        'day' => 7,
                        'from' => null,
                        'to' => null
                    ]
                ]
            ]
        ];
    }
}

