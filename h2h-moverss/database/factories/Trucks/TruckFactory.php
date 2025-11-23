<?php

namespace Database\Factories\Trucks;

use App\Models\Division;
use App\Models\Truck\Truck;
use Database\Factories\BaseFactory;

class TruckFactory extends BaseFactory
{
    protected $model = Truck::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $division = Division::factory()->create();

        return [
            'active' => 1,
            'title' => '#1 Yellow (16FT)',
            'nickname' => 'GMC - 16',
            'division_ids' => [$division->id],
            'vendor' => 'Citroen',
            'model' => 'Truck 4 HDi',
            'year' => '2020',
            'color' => 'White',
            'l_plate' => 'AA0011AA',
            'vin' => '54DC4W1B6JS808553',
            'length' => 0,
            'cuft' => 0,
            'lbs' => 0,
            'start_mi' => 0,
            'cur_mi' => 0,
            'p_color' => '#788c8a',
            'avi_date' => null,
            'reg_date' => null,
            'tech_date' => null,
            'deleted_at' => null,
            'partner_id' => null,
        ];
    }
}


