<?php

namespace Database\Factories\Companies;

use App\Models\Companies\CommercialManager;
use App\Models\Companies\Company;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method CommercialManager|CommercialManager[]|Collection create(array $attributes = [])
 */
class CommercialManagerFactory extends Factory
{
    protected $model = CommercialManager::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->name,
            'email' => new Email($this->faker->unique()->safeEmail),
            'phone' => new Phone($this->faker->unique()->phoneNumber),
        ];
    }
}
