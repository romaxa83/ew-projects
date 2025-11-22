<?php

namespace Database\Factories\OneC;

use App\Models\OneC\Moderator;
use App\ValueObjects\Email;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Moderator[]|Moderator create(array $attributes = [])
 */
class ModeratorFactory extends Factory
{
    protected $model = Moderator::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => new Email($this->faker->unique()->email),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ];
    }
}
