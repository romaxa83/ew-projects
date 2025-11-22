<?php

namespace App\ModelFilters\Users;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Traits\Filters\StatusFilter;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends BaseModelFilter
{
    use StatusFilter;

    public function search(string $name)
    {
        return $this->where(
            function (Builder $b) use ($name) {
                return $b
                    ->whereRaw('lower(concat(first_name, \' \', last_name)) like ?', ['%' . escape_like(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(email) like ?', ['%' . escape_like(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(phone) like ?', ['%' . escape_like(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
            }
        );
    }

    public function role(int|string $value)
    {
        $this->whereHas('roles', fn(Builder $b) => $b->where('id', $value));
    }

    public function roles(array $value)
    {
        $this->whereHas('roles', fn(Builder $b) => $b->whereIn('id', $value));
    }

    protected function allowedOrders(): array
    {
        return User::ALLOWED_SORTING_FIELDS;
    }
}
