<?php

namespace Database\Factories\Users;

use App\Models\Localization\Language;
use App\Models\Users\User;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @method User|User[]|Collection create(array $attributes = [])
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => new Email($this->faker->unique()->safeEmail),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'email_verification_code' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'lang' => Language::default()->first()->slug,
            'phone' => new Phone($this->faker->phoneNumber),
            'deleted_at' => null,
            'guid' => $this->faker->uuid
        ];
    }

    public function notVerified(): self
    {
        return $this->state(
            [
                'email_verified_at' => null,
                'phone_verified_at' => null,
            ]
        );
    }
    public function deleted(): self
    {
        return $this->state(
            [
                'deleted_at' => now(),
            ]
        );
    }

}
