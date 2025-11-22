<?php

namespace Database\Factories\Admin;

use App\Models\Admin\Admin;
use App\ValueObjects\Email;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method static Admin create(array $attributes = [])
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => new Email($this->faker->unique()->safeEmail),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'lang' => app('localization')->getDefaultSlug(),
            'department_type' => null,
        ];
    }
}

