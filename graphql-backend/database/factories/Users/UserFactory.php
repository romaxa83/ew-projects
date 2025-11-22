<?php

namespace Database\Factories\Users;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
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
            'middle_name' => $this->faker->firstName,
            'email' => new Email($this->faker->unique()->safeEmail),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'email_verification_code' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'lang' => Language::default()->first()->slug,
            'phone' => new Phone($this->faker->phoneNumber),
        ];
    }

    public function sameCompany(User $user): self
    {
        return $this->withCompany($user->company);
    }

    public function withCompany(Company|int $company = null, bool $isOwner = false): self
    {
        $withCompany = $company ?: Company::factory()->create();

        $attributes = [
            'company_id' => to_model_key($withCompany),
        ];

        if ($isOwner) {
            $attributes = array_merge(
                [
                    'state' => Company::STATE_OWNER
                ],
                $attributes
            );
        }

        return $this->has(
            CompanyUser::factory()->state($attributes)
        );
    }
}
