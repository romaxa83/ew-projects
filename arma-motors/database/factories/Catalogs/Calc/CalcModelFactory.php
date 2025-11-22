<?php

namespace Database\Factories\Catalogs\Calc;

use App\Models\Catalogs\Calc\CalcModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalcModelFactory extends Factory
{
    protected $model = CalcModel::class;

    public function definition()
    {
        return [
            'brand_id' => 1,
            'model_id' => 10,
            'mileage_id' => 1,
            'engine_volume_id' => 1,
            'transmission_id' => 1,
            'drive_unit_id' => 1,
        ];
    }
}

