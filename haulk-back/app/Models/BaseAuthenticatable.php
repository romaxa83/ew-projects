<?php

namespace App\Models;

use App\Models\Forms\Draft;
use App\Traits\Filterable;
use Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @method static static|Builder query()
 * @method static Builder|static whereEmail(string $email)
 * @method static static|Builder filter(array $attributes = [], $filterClass = null)
 * @method static static[]|Collection get()
 * @method static static|null first()
 * @method static static firstOrFail($id)
 * @method static static[]|Collection|LengthAwarePaginator paginate(...$attributes)
 */
abstract class BaseAuthenticatable extends User
{
    use Filterable;

    public const STATUS_INACTIVE = false;

    public const STATUS_ACTIVE = true;

    public function setPasswordHash(string $hash): void
    {
        $this->attributes['password'] = $hash;
    }

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function updatePassword(string $password): bool
    {
        $this->password = $password;

        return $this->save();
    }

    public function passwordCompare(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function findDraftByPath(string $path): ?Draft
    {
        return $this->drafts()
            ->where('path', $path)
            ->first();
    }

    /**
     * @return HasMany|Draft
     */
    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

}
