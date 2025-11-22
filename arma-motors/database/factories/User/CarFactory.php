<?php

namespace Database\Factories\User;

use App\Models\User\Car;
use App\ValueObjects\CarNumber;
use App\ValueObjects\CarVin;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Car::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'brand_id' => 1,
            'model_id' => 1,
            'user_id' => 1,
            'vin' => new CarVin(\Str::random(8)),
            'number' => new CarNumber(\Str::random(8)),
            'year' =>  '2012',
            'inner_status' => Car::DRAFT,
            'is_verify' => true,
            'is_moderate' => false,
            'is_personal' => true,
            'is_buy' => true,
            'is_add_to_app' => true,
            'selected' => false,
            'is_order' => false,
            'in_garage' => false,
        ];
    }
}
