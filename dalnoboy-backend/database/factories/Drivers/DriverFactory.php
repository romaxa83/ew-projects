<?php

namespace Database\Factories\Drivers;

use App\Models\Clients\Client;
use App\Models\Drivers\Driver;
use App\Traits\Factory\HasPhonesFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Driver[]|Driver create(array $attributes = [])
 */
class DriverFactory extends Factory
{
    use HasPhonesFactory;

    protected $model = Driver::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'second_name' => $this->faker->firstName,
            'email' => $this->faker->safeEmail,
            'comment' => $this->faker->text,
            'client_id' => Client::factory()
        ];
    }

    public function withoutClient(): self
    {
        return $this->state(
            [
                'client_id' => null,
            ]
        );
    }
}
