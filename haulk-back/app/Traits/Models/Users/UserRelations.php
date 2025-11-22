<?php

namespace App\Traits\Models\Users;

use App\Models\Forms\Draft;
use App\Models\Users\AuthHistory;
use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait UserRelations
 *
 * @see User::lastLogin()
 * @property-read AuthHistory $lastLogin
 *
 * @see UserRelations::owned()
 * @property-read Collection|User[] owned
 *
 * @package App\Traits\Models\Users
 */
trait UserRelations
{
    public function owner(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'owner_id');
    }

    public function owned(): HasMany
    {
        return $this->hasMany(User::class, 'owner_id', 'id');
    }

    public function driverInfo(): HasOne
    {
        return $this->hasOne(DriverInfo::class, 'driver_id', 'id');
    }

    public function lastLogin(): HasOne
    {
        return $this->hasOne(AuthHistory::class, 'user_id', 'id')
            ->orderBy('id', 'desc');
    }
}
