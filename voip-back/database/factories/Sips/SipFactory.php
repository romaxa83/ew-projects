<?php

namespace Database\Factories\Sips;

use App\Models\Sips\Sip;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Sip[]|Sip create(array $attributes = [])
 */
class SipFactory extends BaseFactory
{
    protected $model = Sip::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->buildingNumber,
            'password' => $this->faker->firstName,
        ];
    }
}
