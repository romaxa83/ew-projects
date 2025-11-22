<?php

namespace Database\Seeders;

use App\DTO\Catalog\Car\EngineVolumeDTO;
use App\Services\Catalog\Car\EngineVolumeService;

class EngineVolumeSeeder extends BaseSeeder
{

    public function __construct(protected EngineVolumeService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('car_engine_volumes')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = EngineVolumeDTO::byArgs($item);
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
                'volume' => 1.5
            ],
            [
                'sort' => 2,
                'volume' => 1.6
            ],
            [
                'sort' => 3,
                'volume' => 1.8
            ],
            [
                'sort' => 4,
                'volume' => 2.0
            ],
            [
                'sort' => 5,
                'volume' => 2.4
            ],
            [
                'sort' => 6,
                'volume' => 3.0
            ],
            [
                'sort' => 7,
                'volume' => 3.2
            ],
        ];
    }
}



