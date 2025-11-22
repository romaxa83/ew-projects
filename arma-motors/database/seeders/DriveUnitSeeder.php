<?php

namespace Database\Seeders;

use App\DTO\Catalog\Car\DriveUnitDTO;
use App\Services\Catalog\Car\DriveUnitService;

class DriveUnitSeeder extends BaseSeeder
{

    public function __construct(protected DriveUnitService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('drive_units')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = DriveUnitDTO::byArgs($item);
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
                'name' => '2WD',
            ],
            [
                'sort' => 2,
                'name' => '4WD',
            ],
            [
                'sort' => 3,
                'name' => 'AWD',
            ],
        ];
    }
}
