<?php

namespace Wezom\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Quotes\Models\Terminal;

/**
 * @extends Factory<Terminal>
 */
class TerminalFactory extends Factory
{
    protected $model = Terminal::class;

    public function definition(): array
    {
        return [
            'address' => fake()->unique()->address(),
        ];
    }
}
