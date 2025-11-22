<?php

namespace Database\Seeders;

use App\DTO\Catalog\Calc\MileageDTO;
use App\Services\Catalog\Calc\MileageService;

class MileageSeeder extends BaseSeeder
{

    public function __construct(protected MileageService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('mileages')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = MileageDTO::byArgs($item);
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
            ['value' => 1000],
            ['value' => 15000],
            ['value' => 20000],
            ['value' => 25000],
            ['value' => 30000],
            ['value' => 35000],
            ['value' => 40000],
            ['value' => 45000],
            ['value' => 50000],
            ['value' => 55000],
            ['value' => 60000],
        ];
    }
}




