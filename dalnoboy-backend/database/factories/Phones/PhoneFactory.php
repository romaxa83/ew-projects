<?php

namespace Database\Factories\Phones;

use App\Models\Phones\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Phone[]|Phone create(array $attributes = [])
 */
class PhoneFactory extends Factory
{
    protected $model = Phone::class;

    public function definition(): array
    {
        return [
            'phone' => new \App\ValueObjects\Phone($this->faker->ukrainianPhone),
            'is_default' => false
        ];
    }
}
