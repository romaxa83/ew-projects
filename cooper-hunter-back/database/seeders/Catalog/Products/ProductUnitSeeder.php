<?php

namespace Database\Seeders\Catalog\Products;

use App\Models\Catalog\Products\UnitType;
use Illuminate\Database\Seeder;

class ProductUnitSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'indoor',
            'outdoor',
            'monoblock',
            'accessory',
        ];

        foreach ($data as $name) {
            UnitType::updateOrCreate([
                'name' => $name
            ]);
        }
    }
}

