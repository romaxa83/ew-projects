<?php

namespace App\ModelFilters\Users;

use App\Models\Users\User;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserFilter
 *
 * @mixin User
 *
 * @package App\ModelFilters\Users
 */
class UserFilter extends ModelFilter
{
    public function status(string $status)
    {
        $this->where('status', '=', $status);
    }

    public function name(string $name)
    {
        return $this->where(
            function (Builder $query) use ($name) {
                return $query
                    ->whereRaw('lower(concat(first_name, \' \', last_name)) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(email) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(phone) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
            }
        );
    }

    public function role(int $id)
    {
        $this->whereHas(
            'roles',
            function (Builder $builder) use ($id) {
                $builder->where('id', $id);
            }
        );
    }

    public function myDrivers($value)
    {
        if ($value) {
            $this->onlyDrivers()
                ->where('owner_id', Auth::id());
        }
    }

    public function owner(int $owner_id)
    {
        return $this->onlyDrivers()->where('owner_id', $owner_id);
    }

    public function q(string $name)
    {
        return $this->where(
            function (Builder $query) use ($name) {
                return $query
                    ->whereRaw('lower(concat(first_name, \' \', last_name)) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(email) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(phone) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
            }
        );
    }

    public function emailSearch(string $value)
    {
        return $this->whereRaw('lower(email) like ?', ['%' . escapeLike(mb_convert_case($value, MB_CASE_LOWER)) . '%']);
    }

    public function roles(array $roles)
    {
        return $this->whereHas(
            'roles',
            function (Builder $builder) use ($roles) {
                $builder->whereIn('id', $roles);
            }
        );
    }

    public function tag(int $tagId): void
    {
        $this->whereHas(
            'tags',
            fn(Builder $query) => $query->where('id', $tagId)
        );
    }

    public function searchid(int $id): void
    {
        $this->where('id', $id);
    }
}
