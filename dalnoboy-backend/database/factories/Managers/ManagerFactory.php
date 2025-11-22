<?php

namespace Database\Factories\Managers;

use App\Models\Managers\Manager;
use App\Traits\Factory\HasPhonesFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Manager[]|Manager create(array $attributes = [])
 */
class ManagerFactory extends Factory
{
    use HasPhonesFactory;

    protected $model = Manager::class;

    public function definition(): array
    {
        return [
            'first_name' => $firstName = $this->faker->firstName,
            'last_name' => $lastName = $this->faker->lastName,
            'second_name' => $secondName = $this->faker->firstName,
            'region_id' => $this->faker->ukrainianRegionId,
            'city' => $this->faker->city,
            'hash' => md5($firstName . $lastName . $secondName)
        ];
    }
}
