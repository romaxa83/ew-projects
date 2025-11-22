<?php

namespace App\ModelFilters\Saas\Admins;

use App\Models\Admins\Admin;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @mixin Admin
 */
class AdminFilter extends ModelFilter
{
    public const TABLE = Admin::TABLE;

    public function query(string $value): void
    {
        $value = Str::lower($value);

        $this->where(
            static function (Builder $builder) use ($value) {
                $builder
                    ->where(static::TABLE . '.full_name', 'ILIKE', "%$value%")
                    ->orWhere(static::TABLE . '.email', 'ILIKE', "%$value%")
                    ->orWhere(static::TABLE . '.phone', 'ILIKE', "%$value%");
            }
        );
    }

    public function roles(array $values): void
    {
        $this->whereHas(
            'roles',
            static function (Builder $builder) use ($values) {
                $builder->whereIn('id', $values)
                    ->where('guard_name', Admin::GUARD);
            }
        );
    }

    public function order(string $value): void
    {
        $this->orderBy($value, request('order_type') ?? 'asc');
    }
}
