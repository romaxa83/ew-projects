<?php

namespace Database\Factories\Support\Supports;

use App\Models\Support\Supports\Support;
use App\ValueObjects\Phone;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Support[]|Support create(array $attributes = [])
 */
class SupportFactory extends BaseFactory
{
    protected $model = Support::class;

    public function definition(): array
    {
        return [
            'phone' => new Phone($this->faker->e164PhoneNumber)
        ];
    }
}
