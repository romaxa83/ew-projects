<?php

namespace App\Rules;

use App\Models\Permissions\Role;
use App\Models\Permissions\RoleTranslates;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class RoleUniqueNameValidator implements Rule
{

    private string $guard;

    private int|null $id;

    public function __construct(int|null $id, string $guard = User::GUARD)
    {
        $this->guard = $guard;
        $this->id = $id;
    }

    public function passes($attribute, $value): bool
    {
        $exists = Role::query()
            ->where('name', $value)
            ->where('guard_name', $this->guard)
            ->when(
                $this->id,
                function (Builder $query) {
                    return $query->where('id', '!=', $this->id);
                }
            )->exists();
        return !$exists;
    }

    public function message(): string
    {
        return __('validation.role_name_not_unique');
    }
}