<?php

namespace Database\Seeders;

use App\DTO\Catalog\Car\FuelDTO;
use App\Services\Catalog\Car\FuelService;

class FuelSeeder extends BaseSeeder
{

    public function __construct(protected FuelService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('fuels')->truncate();
        \DB::table('fuel_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = FuelDTO::byArgs($item);
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
                        'name' => 'Бензин',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Бензин',
                    ],
                ]
            ],
            [
                'sort' => 2,
                'translations' => [
                    [
                        'lang' => 'ru',
                        'name' => 'Дизель',
                    ],
                    [
                        'lang' => 'uk',
                        'name' => 'Дизель',
                    ],
                ]
            ],
        ];
    }
}




