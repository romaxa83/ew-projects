<?php

namespace Database\Seeders;

use App\DTO\User\LoyaltyDTO;
use App\Models\Catalogs\Car\Brand;
use App\Models\User\Loyalty\Loyalty;
use App\Services\User\LoyaltyService;

class LoyaltySeeder extends BaseSeeder
{
    public function __construct(protected LoyaltyService $service)
    {
        parent::__construct();
    }

    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('loyalties')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = $this->data();

        try {
            \DB::transaction(function () use ($data) {
                foreach ($data as $item){
                    $dto = LoyaltyDTO::byArgs($item);
                    $this->service->create($dto);
                }
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    protected function data(): array
    {
        $renault = Brand::query()->where('name','Renault')->first();
        $mitsubishi = Brand::query()->where('name','Mitsubishi')->first();
        $volvo = Brand::query()->where('name','Volvo')->first();

        return [
            [
                'brandId' => $renault->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '2',
                'discount' => 4.0,
            ],
            [
                'brandId' => $renault->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '3',
                'discount' => 6.0,
            ],
            [
                'brandId' => $renault->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '4',
                'discount' => 8.0,
            ],
            [
                'brandId' => $renault->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '5',
                'discount' => 10.0,
            ],
            [
                'brandId' => $renault->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '5+',
                'discount' => 20.0,
            ],
            [
                'brandId' => $renault->id,
                'type' => Loyalty::TYPE_BYU,
                'discount' => 2.0,
            ],
            [
                'brandId' => $mitsubishi->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '2',
                'discount' => 4.0,
            ],
            [
                'brandId' => $mitsubishi->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '3',
                'discount' => 6.0,
            ],
            [
                'brandId' => $mitsubishi->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '3+',
                'discount' => 25.0,
            ],
            [
                'brandId' => $mitsubishi->id,
                'type' => Loyalty::TYPE_SPARES,
                'age' => '2',
                'discount' => 4.0,
            ],
            [
                'brandId' => $mitsubishi->id,
                'type' => Loyalty::TYPE_SPARES,
                'age' => '3',
                'discount' => 6.0,
            ],
            [
                'brandId' => $mitsubishi->id,
                'type' => Loyalty::TYPE_SPARES,
                'age' => '3+',
                'discount' => 10.0,
            ],
            [
                'brandId' => $mitsubishi->id,
                'type' => Loyalty::TYPE_BYU,
                'discount' => 2.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '3',
                'discount' => 10.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '4',
                'discount' => 10.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '5',
                'discount' => 20.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '6',
                'discount' => 20.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '7',
                'discount' => 20.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SERVICE,
                'age' => '8+',
                'discount' => 30.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SPARES,
                'age' => '3',
                'discount' => 10.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SPARES,
                'age' => '4',
                'discount' => 10.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SPARES,
                'age' => '5',
                'discount' => 20.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SPARES,
                'age' => '6',
                'discount' => 20.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SPARES,
                'age' => '7',
                'discount' => 20.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_SPARES,
                'age' => '8+',
                'discount' => 30.0,
            ],
            [
                'brandId' => $volvo->id,
                'type' => Loyalty::TYPE_BYU,
                'discount' => 1.0,
            ],
        ];
    }
}
