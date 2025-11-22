<?php

namespace Database\Factories\Users;

use App\Enums\Users\UserStatus;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\Models\Users\User;
use Database\Factories\BaseFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\User>
 */
class UserFactory extends BaseFactory
{
    protected $model = User::class;

    protected static ?string $password;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'first_name' => fake()->name(),
            'last_name' => fake()->name(),
            'second_name' => fake()->name(),
            'status' => UserStatus::ACTIVE,
            'email' => new Email(fake()->unique()->safeEmail()),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'email_verified_code' => null,
            'password_verified_code' => null,
            'phone' => null,
            'phone_extension' => null,
            'phones' => [],
            'lang' => 'en',
            'deleted_at' => null,
        ];
    }
}
