<?php

namespace Database\Factories\Admins;

use App\Enums\Permissions\AdminRolesEnum;
use App\Models\Admins\Admin;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Traits\Factory\HasPhonesFactory;
use App\ValueObjects\Email;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * @method static Admin|Admin[]|Collection create(array $attributes = [])
 */
class AdminFactory extends BaseFactory
{
    use HasPhonesFactory;

    public const DEFAULT_PASSWORD = 'password';

    protected $model = Admin::class;

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
        ];
    }

    public function withRole(string $role = AdminRolesEnum::SUPER_ADMIN): self
    {
        return $this->afterCreating(
            function (Admin $admin) use ($role)
            {
                $admin->assignRole(
                    Role::whereName($role)
                        ->first()->id
                );
            }
        );
    }
}
