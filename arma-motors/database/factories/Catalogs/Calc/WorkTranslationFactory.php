<?php

namespace Database\Factories\Catalogs\Calc;

use App\Models\Catalogs\Calc\WorkTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'lang' => 'ru',
            'name' => $this->faker->city,
        ];
    }
}
