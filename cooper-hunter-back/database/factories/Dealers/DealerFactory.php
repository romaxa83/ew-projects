<?php

namespace Database\Factories\Dealers;

use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\ValueObjects\Email;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @method Dealer|Dealer[]|Collection create(array $attributes = [])
 */
class DealerFactory extends Factory
{
    protected $model = Dealer::class;

    public function definition(): array
    {
        return [
            'guid' => $this->faker->uuid,
            'company_id' => Company::factory(),
            'email' => new Email($this->faker->unique()->safeEmail),
            'phone' => null,
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'lang' => 'en',
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_main' => false,
            'is_main_company' => true,
        ];
    }
}
