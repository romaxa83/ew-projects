<?php

namespace Wezom\Core\Database\Factories\Auth;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Core\Models\Auth\GuestSession;

/**
 * @extends Factory<GuestSession>
 */
class GuestSessionFactory extends Factory
{
    protected $model = GuestSession::class;

    public function definition(): array
    {
        return [
            'session' => $this->faker->unique()->uuid(),
            'expires_at' => now()->addMonths(6),
        ];
    }
}
