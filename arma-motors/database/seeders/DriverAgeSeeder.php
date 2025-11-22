<?php

namespace Database\Seeders;

use App\DTO\Catalog\Service\DriverAgeDTO;
use App\Services\Catalog\Service\DriverAgeService;

class DriverAgeSeeder extends BaseSeeder
{

    public function __construct(protected DriverAgeService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('driver_ages')->truncate();
        \DB::table('driver_age_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = DriverAgeDTO::byArgs($item);
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
                        'name' => 'Все водители на законных основаниях возрастом до 23 лет',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Все водители на законных основаниях возрастом до 23 лет',
                    ],
                ]
            ],
            [
                'sort' => 2,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Перечень водителей (до 5 особ) возрастом от 30 до 65 лет',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Перечень водителей (до 5 особ) возрастом от 30 до 65 лет',
                    ],
                ]
            ],
            [
                'sort' => 3,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Перечень водителей (до 5 особ) возрастом от 45 до 55 лет',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Перечень водителей (до 5 особ) возрастом от 45 до 55 лет',
                    ],
                ]
            ]
        ];
    }
}



