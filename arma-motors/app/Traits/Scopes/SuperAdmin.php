<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait SuperAdmin
{
    public function assetSuperAdmin(): void
    {
        if($this->isSuperAdmin()){
            throw new \DomainException(__('error.not manipulate by this user'));
        }
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === config('permission.roles.super_admin');
    }

    public function scopeSuperAdmin(Builder $query)
    {
        return $query->where('admins.name', '=', config('permission.roles.super_admin'));
    }

    public function scopeNotSuperAdmin(Builder $query)
    {
        return $query->where('admins.name', '!=', config('permission.roles.super_admin'));
    }
}
