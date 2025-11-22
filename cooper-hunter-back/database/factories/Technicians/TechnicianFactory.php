<?php

namespace Database\Factories\Technicians;

use App\Models\Localization\Language;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Technicians\Technician;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @method Technician|Technician[]|Collection create(array $attributes = [])
 */
class TechnicianFactory extends Factory
{
    protected $model = Technician::class;

    public function definition(): array
    {
        $country = Country::query()->first();

        return [
            'state_id' => State::factory(),
            'country_id' => $country->id,
            'is_certified' => false,
            'is_verified' => false,
            'is_commercial_certification' => true,
            'hvac_license' => $this->faker->randomNumber(),
            'epa_license' => $this->faker->randomNumber(),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => new Email($this->faker->unique()->safeEmail),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'email_verification_code' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'lang' => Language::default()->first()->slug,
            'phone' => new Phone($this->faker->unique()->phoneNumber),
            'deleted_at' => null,
            'guid' => $this->faker->uuid
        ];
    }

    public function certified(): self
    {
        return $this->state(
            [
                'is_certified' => true,
            ]
        );
    }

    public function verified(): self
    {
        return $this->state(
            [
                'is_verified' => true,
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

    public function emailNotVerified(): self
    {
        return $this->state(
            [
                'email_verified_at' => null,
                'phone_verified_at' => null,
            ]
        );
    }
}
