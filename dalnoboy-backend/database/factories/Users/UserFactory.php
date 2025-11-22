<?php

namespace Database\Factories\Users;

use App\Enums\Permissions\UserRolesEnum;
use App\Models\Branches\Branch;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Models\Users\UserBranch;
use App\Traits\Factory\HasPhonesFactory;
use App\ValueObjects\Email;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @method User|User[]|Collection create(array $attributes = [])
 */
class UserFactory extends BaseFactory
{
    use HasPhonesFactory;

    public const DEFAULT_PASSWORD = 'password';

    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'second_name' => $this->faker->firstName,
            'email' => new Email($this->faker->unique()->safeEmail),
            'password' => Hash::make(self::DEFAULT_PASSWORD),
            'lang' => Language::default()
                ->first()->slug,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'email_verification_code' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): self
    {
        return $this->configurePhone()
            ->afterCreating(
                function (User $user)
                {
                    UserBranch::create(
                        [
                            'user_id' => $user->id,
                            'branch_id' => Branch::factory()
                                ->create()->id
                        ]
                    );
                }
            );
    }

    public function inspector(): self
    {
        return $this->afterCreating(
            fn(User $user) => $user->assignRole(
                Role::whereName(UserRolesEnum::INSPECTOR)
                    ->first()
            )
        );
    }

}
