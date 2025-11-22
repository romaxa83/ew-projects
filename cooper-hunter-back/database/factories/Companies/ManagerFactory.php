<?php

namespace Database\Factories\Companies;

use App\Models\Companies\Company;
use App\Models\Companies\Manager;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Manager|Manager[]|Collection create(array $attributes = [])
 */
class ManagerFactory extends Factory
{
    protected $model = Manager::class;

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
