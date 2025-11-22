<?php

namespace Database\Seeders;

use App\DTO\Catalog\Car\TransmissionDTO;
use App\Services\Catalog\Car\TransmissionService;

class TransmissionSeeder extends BaseSeeder
{

    public function __construct(protected TransmissionService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('transmissions')->truncate();
        \DB::table('transmission_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = TransmissionDTO::byArgs($item);
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
                        'name' => 'Автоматическая',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Автоматична',
                    ],
                ]
            ],
            [
                'sort' => 2,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Механическая',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Механічна',
                    ],
                ]
            ],
            [
                'sort' => 3,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Роботизированая',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Роботизована',
                    ],
                ]
            ],
            [
                'sort' => 4,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Вариативная',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Вариативна',
                    ],
                ]
            ],
        ];
    }
}



