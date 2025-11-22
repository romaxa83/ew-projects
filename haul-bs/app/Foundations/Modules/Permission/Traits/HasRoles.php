<?php

namespace App\Foundations\Modules\Permission\Traits;

use App\Foundations\Modules\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * @see HasRoles::getRoleAttribute()
 * @property-read null|Role $role
 *
 * @see HasRoles::getRoleNameAttribute()
 * @property-read null|string $role_name
 *
 * @see HasRoles::getRoleNamePrettyAttribute()
 * @property-read null|string $role_name_pretty
 *
 * @see HasRoles::roles()
 * @property-read Collection|Role[] $roles
 */

trait HasRoles
{
    use \Spatie\Permission\Traits\HasRoles;

    public function getRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->roles->first()->name;
    }

    public function getRoleNamePrettyAttribute(): ?string
    {
        return ucfirst(remove_underscore($this->getRoleNameAttribute()));
    }

    public function getPermissions(): array
    {
        return $this->role->permissions
            ->groupBy('group')
            ->map(
                fn(Collection $group) => $this->prettyName($group->pluck('name')->toArray())
            )
            ->toArray()
        ;
    }

    private function prettyName(array $data): array
    {
        $tmp = [];
        foreach ($data as $item){
            $tmp[] = last(explode('.', $item));
        }
        return $tmp;
    }
}
