<?php

namespace Database\Seeders;

use App\DTO\Catalog\Service\InsuranceFranchiseDTO;
use App\Services\Catalog\Service\InsuranceFranchiseService;

class InsuranceFranchiseSeeder extends BaseSeeder
{

    public function __construct(protected InsuranceFranchiseService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('insurance_franchise')->truncate();
        \DB::table('service_insurance_franchise_relation')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = InsuranceFranchiseDTO::byArgs($item);
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
                'name' => '0%',
                'insuranceIds' => [12]
            ],
            [
                'sort' => 2,
                'name' => '0.5%',
                'insuranceIds' => [12]
            ],
            [
                'sort' => 3,
                'name' => '1%',
                'insuranceIds' => [12]
            ],
            [
                'sort' => 4,
                'name' => '2%',
                'insuranceIds' => [12]
            ],
            [
                'sort' => 5,
                'name' => "0",
                'insuranceIds' => [10,11]
            ],
            [
                'sort' => 6,
                'name' => '2000',
                'insuranceIds' => [10,11]
            ],
            [
                'sort' => 7,
                'name' => '2600',
                'insuranceIds' => [10,11]
            ],
        ];
    }
}




